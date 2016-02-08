<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Admin\Form\Language;
use Application\Model\Entity\Lang;
use Application\Model\Entity\User;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Validator;
use Zend\Mail;

class LogController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function indexAction()
    {
        return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'in']);
    }

    public function inAction()
    {
        //check for the existence of any users, and if none, it means it is a new installation, then redirect to user registration
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $countAdministrators = $entityManager->getRepository(get_class(new User()))->countUsers();
        if(!$countAdministrators){
            $this->flashMessenger()->addInfoMessage('Here you can create the first user for the system');
            return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'initial']);
        }
        $uname = new Element\Text('uname');
        $uname->setLabel('User name');
        $uname->setAttribute('required', 'required');

        $password = new Element\Password('password');
        $password->setLabel('Password');
        $password->setAttribute('required', 'required');

        $form = new Form('login');
        $form->add($uname)->add($password);

        $unameInput = new Input('uname');
        $unameInput->getFilterChain()->attachByName('StringTrim');

        $passwordInput = new Input('password');

        $inputFilter = new InputFilter();
        $inputFilter->add($unameInput)->add($passwordInput);

        $form->setInputFilter($inputFilter);

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $uname = $form->get('uname')->getValue();
                $password = $form->get('password')->getValue();

                $auth = $this->getServiceLocator()->get('auth');
                $authAdapter = $auth->getAdapter();
                $authAdapter->setIdentity($uname);
                $authAdapter->setCredential($password);

                $result = $auth->authenticate();
                $user = $result->getIdentity();
                if($result->isValid()){
                    $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate("Welcome %s. You have been logged in successfully"), $user->getUname()));
                    return $this->redir()->toRoute('admin/default', array('controller' => 'index'));

                }else{
                    $this->flashMessenger()->addErrorMessage($this->translator->translate('Wrong details'));
                    $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
                }
            }
        }

        return array('form' => $form);
    }

    public function outAction()
    {
        $auth = $this->getServiceLocator()->get('auth');
        $auth->clearIdentity();
        $this->flashMessenger()->addSuccessMessage($this->translator->translate('You have been logged out successfully'));
        return $this->redir()->toRoute('admin/default');
    }

    public function forgottenAction()
    {//v_todo - improve the way for password retrieval
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
                        //generate new password memorize it in the DB and send it to the given email
                        $newPassword = $userEntity::generateRandomPassword();
                        $user->setUpass($newPassword);
                        $entityManager->persist($user);
                        $entityManager->flush();

                        //send email with the generated password
                        $config = $this->getServiceLocator()->get('config');

                        $message = new Mail\Message();
                        $message->setFrom($config['other']['no-reply'])
                            ->setTo($email)
                            ->setSubject('New password')
                            ->setBody(sprintf("Dear user, here is your new password: %s", $newPassword));

                        $transport = new Mail\Transport\Sendmail();
                        $transport->send($message);

                        $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate("A new password was generated and sent to %s"), $email));
                        return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
                    }
                }

            }
        }

        return array('form' => $form);
    }

    public function initialAction()
    {
        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $user = $serviceLocator->get('user-entity');
        $form = new \Admin\Form\User($user, $entityManager);

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
                $newPassword = $form->getInputFilter()->get('password')->getValue();
                if($newPassword)
                    $user->setUpass($form->getInputFilter()->get('password')->getValue());
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
