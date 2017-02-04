<?php

namespace Logic\Core\Admin;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Model\Entity\User;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Login
{
    public static function inGet(EntityManagerInterface $em):array
    {
        $countAdministrators = $em->getRepository(User::class)->countAdminUsers();
        if(!$countAdministrators){//check for the existence of any users, and if none, it means it is a new installation, then redirect to user registration
            return[
                'error' => true,
                'message' => 'Here you can create the first user for the system'
            ];
        }

        $form = self::loginForm();
        return [
            'error' => false,
            'form' => $form
        ];
    }

    /**
     * @param array $data The post data
     * @param AuthenticationService $auth
     * @return array
     */
    public static function inPost(array $data, AuthenticationService $auth):array
    {
        $form = self::loginForm();
        $form->setData($data);
        
        if($form->isValid()){
            $uname = $form->get('uname')->getValue();
            $password = $form->get('password')->getValue();

            $authAdapter = $auth->getAdapter();
            $authAdapter->setIdentity($uname);
            $authAdapter->setCredential($password);

            $result = $auth->authenticate();
            if($result->isValid()){
                $user = $result->getIdentity();
                return [
                    'error' => false,
                    'user' => $user,
                    'message' => "Welcome %s. You have been logged in successfully",
                    'form' => $form
                ];
            }
        }

        return [
            'error' => true,
            'message' => 'Wrong details',
            'form' => $form,
        ];
    }

    /**
     * @return Form
     */
    protected static function loginForm()
    {
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

        return $form;
    }
}