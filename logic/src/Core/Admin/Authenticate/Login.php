<?php

namespace Logic\Core\Admin\Authenticate;

use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Login
{
    const ERR_NO_ADMIN = 'auth.login.no-admin';
    const ERR_WRONG_DETAILS = 'auth.login.wrong-details';

    protected $translator;

    public function __construct(ITranslator $translator)
    {
        $this->translator = $translator;
    }
    
    public function inGet(EntityManagerInterface $em):array
    {
        $countAdministrators = $em->getRepository(User::class)->countAdminUsers();
        if(!$countAdministrators){//check for the existence of any users, and if none, it means it is a new installation, then redirect to user registration
            return[
                'status' => self::ERR_NO_ADMIN,
                'message' => $this->translator->translate('Here you can create the first user for the system')
            ];
        }

        $form = self::loginForm();
        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $form
        ];
    }

    /**
     * @param AuthenticationService $auth
     * @param Form $form
     * @param array $data The post data
     * @return array
     */
    public function inPost(AuthenticationService $auth, Form $form, array $data):array
    {
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
                    'status' => StatusCodes::SUCCESS,
                    'user' => $user,
                    'message' => $this->translator->translate("Welcome %s. You have been logged in successfully"),
                    'form' => $form
                ];
                
            }else{
                return [
                    'status' => self::ERR_WRONG_DETAILS,
                    'message' => $this->translator->translate('Wrong details'),
                    'form' => $form,
                ];
            }
        }

        return [
            'status' => StatusCodes::ERR_INVALID_FORM,
            'message' => $this->translator->translate('Check the form for errors'),
            'form' => $form,
        ];
    }

    /**
     * @return Form
     */
    public static function loginForm()
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