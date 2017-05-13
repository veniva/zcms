<?php

namespace Logic\Core\Admin\Authenticate;

use Doctrine\ORM\EntityManagerInterface;
use Veniva\Lbs\Adapters\Interfaces\ITranslator;
use Veniva\Lbs\BaseLogic;
use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\User;
use Veniva\Lbs\Result;
use Zend\Authentication\AuthenticationService;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Login extends BaseLogic
{
    const ERR_NO_ADMIN = 'auth.login.no-admin';
    const ERR_WRONG_DETAILS = 'auth.login.wrong-details';
    /** @var Form */
    protected $form;

    public function __construct(ITranslator $translator)
    {
        parent::__construct($translator);
    }

    public function inGet(EntityManagerInterface $em): Result
    {
        $countAdministrators = $em->getRepository(User::class)->countAdminUsers();
        if(!$countAdministrators){//check for the existence of any users, and if none, it means it is a new installation, then redirect to user registration
            return $this->result(self::ERR_NO_ADMIN, 'Here you can create the first user for the system');
        }

        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->getForm()
        ]);
    }

    /**
     * @param AuthenticationService $auth
     * @param array $data
     * @return \Veniva\Lbs\Result
     */
    public function inPost(AuthenticationService $auth, array $data): Result
    {
        $result = $this->validateFrom($data, $identity, $password);
        if ($result->status !== StatusCodes::SUCCESS) {
            return $result;
        }

        $authAdapter = $auth->getAdapter();
        $authAdapter->setIdentity($identity);
        $authAdapter->setCredential($password);

        $result = $auth->authenticate();
        if($result->isValid()){
            $user = $result->getIdentity();
            return $this->result(StatusCodes::SUCCESS, "Welcome %s. You have been logged in successfully", [
                'user' => $user
            ]);

        }else{
            return $this->result(self::ERR_WRONG_DETAILS, 'Wrong details');
        }
    }

    public function validateFrom(array $data, string &$identity = null, string &$password = null): Result
    {
        $form = $this->getForm();
        $form->setData($data);
        if ($form->isValid()) {
            $identity = $form->get('uname')->getValue();
            $password = $form->get('password')->getValue();

            return $this->result(StatusCodes::SUCCESS);
        }

        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'form' => $form
        ]);
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        if($this->form instanceof Form)
            return $this->form;

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

        $this->form = $form;
        return $this->form;
    }

    /**
     * @param Form $form
     * @return Login
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
}