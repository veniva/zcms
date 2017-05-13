<?php

namespace Logic\Core\Admin\Authenticate;

use Doctrine\ORM\EntityManagerInterface;
use Veniva\Lbs\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\User;
use Logic\Core\Admin\Form\Register as RegisterForm;

class Register
{
    const ERR_USER_EXISTS = 1;
    const ERR_INVALID_FORM = 2;
    
    /** @var EntityManagerInterface */
    protected $em;

    /** @var User */
    protected $user;

    /** @var RegisterForm */
    protected $registerForm;
    
    public function __construct(EntityManagerInterface $em, User $user, RegisterForm $registerForm)
    {
        $this->em = $em;
        $this->user = $user;
        $this->registerForm = $registerForm;
    }
    
    public function getAction()
    {
        //check if user already exists
        if($this->hasUsers()){
            return [
                'status' => self::ERR_USER_EXISTS
            ];
        }

        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $this->registerForm,
        ];
    }

    public function postAction(array $data)
    {
        //check if user already exists
        if($this->hasUsers()){
            return [
                'status' => self::ERR_USER_EXISTS
            ];
        }
        
        $user = $this->user;
        $form = $this->registerForm;
        $form->setData($data);
        $form->isValid();
        if($form->isValid()){
            $password = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
            $user->setUpass($password);
            $user->setRegDate();
            $user->setRole(User::USER_SUPER_ADMIN);
            $this->em->persist($user);

            $lang = new Lang();
            $lang->setIsoCode($form->getInputFilter()->getInputs()['isoCode']->getValue());
            $lang->setName($form->getInputFilter()->getInputs()['language_name']->getValue());
            $lang->setStatus($lang::STATUS_DEFAULT);
            $this->em->persist($lang);

            $this->em->flush();

            return [
                'status' => StatusCodes::SUCCESS,
                'message' => 'The user has been added successfully. Please log below.',
                'lang_iso' => $lang->getIsoCode()
            ];
        }

        return [
            'status' => self::ERR_INVALID_FORM,
            'form' => $form
        ];
    }

    protected function hasUsers(): bool
    {
        return (bool)$this->em->getRepository(User::class)->countAdminUsers();
    }
}