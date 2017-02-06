<?php

namespace Logic\Core\Admin\Authenticate;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Logic\Core\Admin\Interfaces\Authenticate\IResetPassword;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;
use Zend\Form\Form;

class ResetPassword implements IResetPassword
{
    const ERR_BROKEN_LINK = 1;
    const ERR_PASSWORD_REQUEST_NOT_FOUND = 2;
    const ERR_LINK_TOO_OLD = 3;
    const ERR_UNEXISTING_USER = 4;
    const ERR_INVALID_FORM = 5;
    
    /** @var  IRequest */
    protected $request;
    
    /** @var  EntityManagerInterface */
    protected $em;
    
    public function __construct(IRequest $request, EntityManagerInterface $em)
    {
        $this->request = $request;
        $this->em = $em;
    }

    public function resetGet(): array
    {
        $protect = $this->protect();
        if($protect['status'] !== StatusCodes::SUCCESS){
            return $protect;
        }
        
        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $this->form()
        ];
    }
    
    public function resetPost(): array
    {
        $protect = $this->protect();
        if($protect['status'] !== StatusCodes::SUCCESS){
            return $protect;
        }
        $user = $protect['user'];
        
        $form = $this->form();
        $form->setData($this->request->getPost());
        if($form->isValid()){
            $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $this->em->getRepository(PasswordResets::class)->deleteAllForEmail($user->getEmail());
            $this->em->flush();
            
            return [
                'status' => StatusCodes::SUCCESS,
                'message' => 'The password has been changed successfully.'
            ];
        }
        
        return [
            'status' => self::ERR_INVALID_FORM,
            'form' => $form
        ];
    }
    
    public function form(): Form
    {
        $form = new Form('reset_password');
        $form->add(array(
            'type' => 'Admin\Form\UserPassword',
            'name' => 'password_fields'
        ));
        $form->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));
        $form->getInputFilter()->get('password_fields')->get('password_repeat')->setRequired(true);
        
        return $form;
    }
    
    protected function protect(): array
    {
        $email = urldecode($this->request->getQuery('email'));
        $token = $this->request->getQuery('token');
        if(empty($email) || empty($token)){
            return [
                'status' => self::ERR_BROKEN_LINK,
                'message' => 'The link you\'re using is broken'
            ];
        }

        $resetPassword = $this->em->find(PasswordResets::class, ['email' => $email, 'token' => $token]);
        if(!$resetPassword){
            return [
                'status' => self::ERR_PASSWORD_REQUEST_NOT_FOUND,
                'message' => 'The link you\'re using is corrupted, please create another password request'
            ];
        }

        $createdAt = $resetPassword->getCreatedAt();
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT24H'));
        if($date > $createdAt){
            return [
                'status' => self::ERR_LINK_TOO_OLD,
                'message' => 'The link you\'re using is out of date, please create another password request'
            ];
        }

        $user = $this->em->getRepository(User::class)->findOneByEmail($email);
        if(!$user){
            return [
                'status' => self::ERR_UNEXISTING_USER,
                'message' => 'There is no user with email: '.$email.'. You have probably changed the email recently.'
            ];
        }
        
        return [
            'status' => StatusCodes::SUCCESS,
            'user' => $user
        ];
    }
}