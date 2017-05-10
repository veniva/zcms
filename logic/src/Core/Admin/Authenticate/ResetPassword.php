<?php

namespace Logic\Core\Admin\Authenticate;

use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Form\ResetPassword as ResetPasswordForm;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;
use Logic\Core\Result;

class ResetPassword extends BaseLogic
{
    const ERR_BROKEN_LINK = 'reset.broken_link';
    const ERR_PASSWORD_REQUEST_NOT_FOUND = 'reset.pass_not_found';
    const ERR_LINK_TOO_OLD = 'reset.too_old';
    const ERR_UNEXISTING_USER = 'reset.no_user';

    protected $email;
    protected $token;
    
    /** @var  EntityManagerInterface */
    protected $em;
    /** @var ResetPasswordForm */
    protected $form;
    /** @var ITranslator */
    protected $translator;
    
    public function __construct(
        EntityManagerInterface $em, 
        ResetPasswordForm $form, 
        ITranslator $translator,
        string $email = null, 
        string $token = null
    )
    {
        parent::__construct($translator);
        
        $this->em = $em;
        $this->email = $email;
        $this->token = $token;
        $this->form = $form;
        $this->translator = $translator;
    }

    public function resetGet(): Result
    {
        $protect = $this->protect();
        if($protect->status !== StatusCodes::SUCCESS){
            return $protect;
        }
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form
        ]);
    }
    
    public function resetPost(array $data): Result
    {
        $protect = $this->protect();
        if($protect->status !== StatusCodes::SUCCESS){
            return $protect;
        }
        
        $user = $protect->get('user');
        $form = $this->form;
        $form->setData($data);
        if($form->isValid()){
            $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $this->em->getRepository(PasswordResets::class)->deleteAllForEmail($user->getEmail());
            $this->em->flush();
            
            return $this->result(StatusCodes::SUCCESS, 'The password has been changed successfully.');
        }
        
        return $this->result(StatusCodes::ERR_INVALID_FORM, null, [
            'form' => $form
        ]);
    }
    
    protected function protect(): Result
    {
        $email = urldecode($this->email);
        $token = $this->token;
        if(empty($email) || empty($token)){
            return $this->result(self::ERR_BROKEN_LINK, 'The link you\'re using is broken');
        }

        $resetPassword = $this->em->find(PasswordResets::class, ['email' => $email, 'token' => $token]);
        if(!$resetPassword){
            return $this->result(self::ERR_PASSWORD_REQUEST_NOT_FOUND, 'The link you\'re using is out of date or corrupted, please create another password request.');
        }

        $createdAt = $resetPassword->getCreatedAt();
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT24H'));
        if($date > $createdAt){
            return $this->result(self::ERR_LINK_TOO_OLD, 'The link you\'re using is out of date, please create another password request');
        }

        $user = $this->em->getRepository(User::class)->findOneByEmail($email);
        if(!$user){
            return $this->result(self::ERR_UNEXISTING_USER, 'There is no user with email: '.$email.'. You have probably changed the email recently.');
        }
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'user' => $user
        ]);
    }
}