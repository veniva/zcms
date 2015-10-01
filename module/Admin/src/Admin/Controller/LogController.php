<?php

namespace Admin\Controller;


use Application\Service\Invokable\Misc;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Validator;
use Zend\Mail;

class LogController extends AbstractActionController
{
    public function indexAction()
    {
        $this->redirect()->toRoute('admin', ['controller' => 'log', 'action' => 'in']);
    }

    public function inAction()
    {
        $email = new Element\Email('email');
        $email->setLabel('Email');
        $email->setAttribute('required', 'required');

        $password = new Element\Password('password');
        $password->setLabel('Password');
        $password->setAttribute('required', 'required');

        $form = new Form('login');
        $form->add($email)->add($password);

        $emailInput = new Input('email');
        $emailInput->getFilterChain()->attachByName('StringTrim');
        $emailInput->getValidatorChain()->attachByName('EmailAddress');

        $passwordInput = new Input('password');

        $inputFilter = new InputFilter();
        $inputFilter->add($emailInput)->add($passwordInput);

        $form->setInputFilter($inputFilter);

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $email = $form->get('email')->getValue();
                $password = $form->get('password')->getValue();

                $auth = $this->getServiceLocator()->get('auth');
                $authAdapter = $auth->getAdapter();
                $authAdapter->setIdentity($email);
                $authAdapter->setCredential($password);

                $result = $auth->authenticate();
                $user = $result->getIdentity();
                if($result->isValid()){
                    $this->flashMessenger()->addSuccessMessage(sprintf("Welcome %s. You have been logged in successfully", $user->getUname()));//V_TODO- i18n
                    $this->redir()->toRoute('admin', array('controller' => 'index'));

                }else{
                    $this->flashMessenger()->addErrorMessage("Wrong details");//V_TODO- i18n
                    $this->redirect()->toRoute('admin', array('controller' => 'log', 'action' => 'in'));
                }
            }
        }

        return array('form' => $form);
    }

    public function outAction()
    {
        $auth = $this->getServiceLocator()->get('auth');
        $auth->clearIdentity();
        $this->flashMessenger()->addSuccessMessage('You have been logged out successfully');//V_TODO - translate
        return $this->redir()->toRoute('admin');
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
                    $this->flashMessenger()->addErrorMessage("The email entered is not present in our database");
                    $this->redirect()->toRoute('admin', array('controller' => 'log', 'action' => 'forgotten'));
                }else{
                    //Check if the user is administrator
                    $accessControlList = $this->getServiceLocator()->get('acl');
                    $allowed = $accessControlList->isAllowed($user->getRole(), 'index');
                    if(!$allowed){
                        $this->flashMessenger()->addErrorMessage(sprintf("The user with email %s does not have administrative privileges", $email));
                        $this->redirect()->toRoute('admin', array('controller' => 'log', 'action' => 'forgotten'));

                    }else{
                        //generate new password memorize it in the DB and send it to the given email
                        $newPassword = $user->generateRandomPassword();
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

                        $this->flashMessenger()->addSuccessMessage("A new password was generated and sent to ".$email);
                        $this->redirect()->toRoute('admin', array('controller' => 'log', 'action' => 'in'));
                    }
                }

            }
        }

        return array('form' => $form);
    }
}
