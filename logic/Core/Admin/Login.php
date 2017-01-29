<?php

namespace Logic\Core\Admin;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Logic\Core\Model\Entity\User;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Login
{
    /** @var  EntityManager */
    protected $em;

    /** @var  IRequest */
    protected $request;

    public function __construct(EntityManager $em, IRequest $request)
    {
        $this->em = $em;
        $this->request = $request;
    }

    public function in(AuthenticationService $auth):array
    {
        $countAdministrators = $this->em->getRepository(User::class)->countAdminUsers();
        if(!$countAdministrators){//check for the existence of any users, and if none, it means it is a new installation, then redirect to user registration
            return[
                'request' => 'any',
                'error' => true,
                'message' => 'Here you can create the first user for the system'
            ];
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

        $request = $this->request;
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $uname = $form->get('uname')->getValue();
                $password = $form->get('password')->getValue();

                $authAdapter = $auth->getAdapter();
                $authAdapter->setIdentity($uname);
                $authAdapter->setCredential($password);

                $result = $auth->authenticate();
                $user = $result->getIdentity();
                if($result->isValid()){
                    return [
                        'user' => $user,
                        'request' => 'post',
                        'error' => false,
                        'message' => "Welcome %s. You have been logged in successfully",
                        'form' => $form
                    ];
                }else{
                    return [
                        'request' => 'post',
                        'error' => true,
                        'message' => 'Wrong details',
                        'form' => $form,
                    ];
                }
            }
        }

        return [
            'request' => 'any',
            'error' => false,
            'form' => $form
        ];
    }
}