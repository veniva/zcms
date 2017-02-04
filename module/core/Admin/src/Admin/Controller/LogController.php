<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Admin\Form\Language;
use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\Admin;
use Logic\Core\Admin\Authenticate\RestorePassword;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;
use Logic\Core\Stdlib\Strings;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Validator;
use Zend\Mail;

class LogController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function indexAction()
    {
        return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'in']);
    }

    public function inAction()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $auth = $this->getServiceLocator()->get('auth');
        $request = $this->getRequest();
        $lRequest = new Request($request);

        if(!$lRequest->isPost()){
            $data = Admin\Login::inGet($entityManager);
            if($data['error']){
                $this->flashMessenger()->addInfoMessage($this->translator->translate($data['message']));
                return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'initial']);
            }
            
        }else{
            $data = Admin\Login::inPost($lRequest->getPost(), $auth);
            if($data['error'] === true){
                $this->flashMessenger()->addErrorMessage($this->translator->translate($data['message']));
                $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));

            }else{
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate($data['message']), $data['user']->getUname()));
                return $this->redir()->toRoute('admin/default', array('controller' => 'index'));
            }
        }

        return [
            'form' => $data['form']
        ];
    }

    public function outAction()
    {
        $auth = $this->getServiceLocator()->get('auth');
        Admin\Logout::logout($auth);
        $this->flashMessenger()->addSuccessMessage($this->translator->translate('You have been logged out successfully'));
        return $this->redir()->toRoute('admin/default');
    }

    public function forgottenAction()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $request = new Request($this->getRequest());
        if($request->isPost()){

            $result = RestorePassword::postAction($request->getPost(), $entityManager);

            if($result['status'] == RestorePassword::ERR_NOT_FOUND){
                $this->flashMessenger()->addErrorMessage($this->translator->translate($result['message']));
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));
                
            }
            else if($result['status'] == RestorePassword::ERR_INVALID_FORM){
                return array('form' => $result['form']);
            }
            else if($result['status'] == RestorePassword::ERR_NOT_ALLOWED){
                $this->flashMessenger()->addErrorMessage(sprintf($this->translator->translate($result['message']), $result['email']));
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));
            }
            
            //generate email data
            $token = Strings::randomString(10);
            $uri = $this->getRequest()->getUri();
            $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
            $basePath = $renderer->basePath('/admin/log/reset');
            $baseUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
            $link = $baseUrl . $basePath. '?email=' . urlencode($result['email']) . '&token=' . $token;
            $message = sprintf($this->translator->translate("Dear user,%sFollowing the new password request, here is a link for you to visit in order to create a new password:%s%s"), "\n\n", "\n\n", $link);
            
            $config = $this->getServiceLocator()->get('config');
            $data = [
                'email' => $result['email'],
                'token' => $token,
                'no-reply' => $config['other']['no-reply'],
                'message' => $message
            ];

            $result = RestorePassword::persistAndSendEmail($entityManager, $this->getServiceLocator()->get('send-mail'), $data);

            $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate($result['message']), $result['email']));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
        }

        $form = Admin\Authenticate\RestorePassword::getAction();
        return array('form' => $form);
    }

    public function resetAction()
    {
        $request = $this->getRequest();
        $email = urldecode($request->getQuery()->email);
        $token = $request->getQuery()->token;
        if(empty($email) || empty($token)){
            $this->flashMessenger()->addErrorMessage($this->translator->translate('The link you\'re using is broken'));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
        }
        $sl = $this->getServiceLocator();
        $entityManager = $sl->get('entity-manager');
        //check if the token is valid
        $passwordResetClassName = get_class(new PasswordResets(null,null));
        $resetPassword = $entityManager->find($passwordResetClassName, ['email' => $email, 'token' => $token]);
        if(!$resetPassword){
            $this->flashMessenger()->addErrorMessage($this->translator->translate('The link you\'re using is out of date or corrupted, please create another password request'));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));
        }
        $createdAt = $resetPassword->getCreatedAt();
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT24H'));
        if($date > $createdAt){
            $this->flashMessenger()->addErrorMessage($this->translator->translate('The link you\'re using is out of date, please create another password request'));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));
        }
        $userClassEntity = $sl->get('user-entity');
        $user = $entityManager->getRepository(get_class($userClassEntity))->findOneByEmail('ven.iv@gmx.com');
        if(!$user){
            $this->flashMessenger()->addErrorMessage($this->translator->translate('There is no user with email: '.$email.'. You have probably changed the email recently.'));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));
        }

        $form = new Form('reset_password');
        $form->add(array(
            'type' => 'Admin\Form\UserPassword',
            'name' => 'password_fields'
        ));
        $form->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));
        $form->getInputFilter()->get('password_fields')->get('password_repeat')->setRequired(true);

        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
                $entityManager->getRepository($passwordResetClassName)->deleteAllForEmail($email);
                $entityManager->flush();
                $this->flashMessenger()->addSuccessMessage($this->getTranslator()->translate('The password has been changed successfully.'));
                $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
            }
        }

        return [
            'form' => $form
        ];
    }

    public function initialAction()
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $user = $serviceLocator->get('user-entity');

        //check if user already exists
        $numberUsers = $entityManager->getRepository(get_class($user))->countAdminUsers();
        if($numberUsers)
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));

        $form = new \Admin\Form\User($user, $entityManager);
        $form->get('submit')->setValue('Submit');

        //region add language name + select flag
        $languageForm = new Language($this->getServiceLocator());
        $form->add($languageForm->get('isoCode'));
        $languageName = $languageForm->get('name');
        $languageName->setName('language_name');
        $form->add($languageName);
        $form->getInputFilter()->add($languageForm->getInputFilter()->get('isoCode'));
        $languageNameInputFilter = $languageForm->getInputFilter()->get('name');
        $languageNameInputFilter->setName($languageName->getName());
        $form->getInputFilter()->add($languageNameInputFilter);
        //endregion

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            //set the role field to not required
            $form->getInputFilter()->get('role')->setRequired(false);
            if($form->isValid()){
                $newPassword = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
                if($newPassword)
                    $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
                $user->setRegDate();
                $user->setRole(User::USER_SUPER_ADMIN);
                $entityManager->persist($user);
                $lang = new Lang();
                $lang->setIsoCode($form->getInputFilter()->getInputs()['isoCode']->getValue());
                $lang->setName($form->getInputFilter()->getInputs()['language_name']->getValue());
                $lang->setStatus($lang::STATUS_DEFAULT);
                $entityManager->persist($lang);

                $entityManager->flush();
                $langCode = $lang->getIsoCode();
                $locale = $locale = ($langCode != 'en') ? $langCode.'_'.strtoupper($langCode) : 'en_US';
                $this->flashMessenger()->addSuccessMessage($this->translator->translate("The user has been added successfully. Please log below.", 'default', $locale));
                return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'in']);
            }
        }
        return [
            'form' => $form,
            'flagCode' => $this->getRequest()->isPost() ? $this->params()->fromPost('isoCode') : null
        ];
    }
}
