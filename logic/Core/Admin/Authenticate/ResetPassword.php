<?php

namespace Logic\Core\Admin\Authenticate;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Form\ResetPassword as ResetPasswordForm;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;

class ResetPassword
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
        $this->em = $em;
        $this->email = $email;
        $this->token = $token;
        $this->form = $form;
        $this->translator = $translator;
    }

    public function resetGet(): array
    {
        $protect = $this->protect();
        if($protect['status'] !== StatusCodes::SUCCESS){
            return $protect;
        }
        
        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $this->form
        ];
    }
    
    public function resetPost(array $data): array
    {
        $protect = $this->protect();
        if($protect['status'] !== StatusCodes::SUCCESS){
            return $protect;
        }
        $user = $protect['user'];
        $form = $this->form;
        $form->setData($data);
        if($form->isValid()){
            $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $this->em->getRepository(PasswordResets::class)->deleteAllForEmail($user->getEmail());
            $this->em->flush();
            
            return [
                'status' => StatusCodes::SUCCESS,
                'message' => $this->translator->translate('The password has been changed successfully.')
            ];
        }
        
        return [
            'status' => StatusCodes::ERR_INVALID_FORM,
            'form' => $form
        ];
    }
    
    protected function protect(): array
    {
        $email = urldecode($this->email);
        $token = $this->token;
        if(empty($email) || empty($token)){
            return [
                'status' => self::ERR_BROKEN_LINK,
                'message' => $this->translator->translate('The link you\'re using is broken')
            ];
        }

        $resetPassword = $this->em->find(PasswordResets::class, ['email' => $email, 'token' => $token]);
        if(!$resetPassword){
            return [
                'status' => self::ERR_PASSWORD_REQUEST_NOT_FOUND,
                'message' => $this->translator->translate('The link you\'re using is corrupted, please create another password request')
            ];
        }

        $createdAt = $resetPassword->getCreatedAt();
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT24H'));
        if($date > $createdAt){
            return [
                'status' => self::ERR_LINK_TOO_OLD,
                'message' => $this->translator->translate('The link you\'re using is out of date, please create another password request')
            ];
        }

        $user = $this->em->getRepository(User::class)->findOneByEmail($email);
        if(!$user){
            return [
                'status' => self::ERR_UNEXISTING_USER,
                'message' => $this->translator->translate('There is no user with email: '.$email.'. You have probably changed the email recently.')
            ];
        }
        
        return [
            'status' => StatusCodes::SUCCESS,
            'user' => $user
        ];
    }
}