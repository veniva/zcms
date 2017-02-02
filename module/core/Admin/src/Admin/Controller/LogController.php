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
use Logic\Core\Admin\Login;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
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
        $login = new Login($entityManager, new Request($request));
        $data = $login->inHttp($auth);

        if($request->isGet() && $data['error'] === true){
            $this->flashMessenger()->addInfoMessage($this->translator->translate($data['message']));
            return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'initial']);

        }
        
        if($request->isPost()){
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
        $login = new Login($this->getServiceLocator()->get('entity-manager'), new Request($this->getRequest()));
        $login->out($auth);
        $this->flashMessenger()->addSuccessMessage($this->translator->translate('You have been logged out successfully'));
        return $this->redir()->toRoute('admin/default');
    }

    public function forgottenAction()
    {
        $email = new Element\Email('email');
        $email->setLabel('Registered email');
        $email->setAttribute('required', 'required');

        $form = new Form('password_forgotten');
        $form->add($email);

        $emailInput = new Input();
        $emailInput->getFilterChain()->attachByName('StringTrim');
        $emailInput->getValidatorChain()->attachByName('EmailAddress');

        $inputFilter = new InputFilter();
        $inputFilter->add($emailInput);

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $email = $form->get('email')->getValue();//get the filtered value

                //Check if the email is present in the DB
                $entityManager = $this->getServiceLocator()->get('entity-manager');
                $userEntity = $this->getServiceLocator()->get('user-entity');
                $user = $entityManager->getRepository(get_class($userEntity))->findOneByEmail($email);
                if(!$user){
                    $this->flashMessenger()->addErrorMessage($this->translator->translate("The email entered is not present in our database"));
                    return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));
                }else{
                    //Check if the user is administrator
                    $accessControlList = $this->getServiceLocator()->get('acl');
                    $allowed = $accessControlList->isAllowed($user->getRoleName(), 'index');
                    if(!$allowed){
                        $this->flashMessenger()->addErrorMessage(sprintf($this->translator->translate("The user with email %s does not have administrative privileges"), $email));
                        return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'forgotten'));

                    }else{
                        //generate token and send a link to the email
                        $stdStrings = $this->getServiceLocator()->get('stdlib-strings');
                        $token = $stdStrings->randomString(10);

                        $passwordResetsEntity = new PasswordResets($email, $token);

                        //use this request to also delete password requests older than 24 hours
                        $entityManager->getRepository(get_class($passwordResetsEntity))->deleteOldRequests();

                        $entityManager->persist($passwordResetsEntity);
                        $entityManager->flush();

                        $uri = $this->getRequest()->getUri();
                        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
                        $basePath = $renderer->basePath('/admin/log/reset');
                        $baseUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
                        $link = $baseUrl . $basePath. '?email=' . urlencode($email) . '&token=' . $token;

                        //send email with the generated password
                        $config = $this->getServiceLocator()->get('config');

                        $message = new Mail\Message();
                        $message->setFrom($config['other']['no-reply'])
                            ->setTo($email)
                            ->setSubject('New password')
                            ->setBody(sprintf($this->translator->translate("Dear user,%sFollowing the new password request, here is a link for you to visit in order to create a new password:%s%s"), "\n\n", "\n\n", $link));

                        $transport = new Mail\Transport\Sendmail();
                        $transport->send($message);

                        $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate("A link was generated and sent to %s"), $email));
                        return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
                    }
                }

            }
        }

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
