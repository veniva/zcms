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
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\User;
use Zend\Form\Element;
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
