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
    
    public function resetGet(IRequest $request, EntityManagerInterface $em): array
    {
        $protect = $this->protect($request, $em);
        if($protect['status'] !== StatusCodes::SUCCESS){
            return $protect;
        }
        
        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $this->form()
        ];
    }
    
    public function resetPost(IRequest $request, EntityManagerInterface $em): array
    {
        $protect = $this->protect($request, $em);
        if($protect['status'] !== StatusCodes::SUCCESS){
            return $protect;
        }
        $user = $protect['user'];
        
        $form = $this->form();
        $form->setData($request->getPost());
        if($form->isValid()){
            $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $em->getRepository(PasswordResets::class)->deleteAllForEmail($user->getEmail());
            $em->flush();
            
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
    
    protected function protect(IRequest $request, EntityManagerInterface $em): array
    {
        $email = urldecode($request->getQuery('email'));
        $token = $request->getQuery('token');
        if(empty($email) || empty($token)){
            return [
                'status' => self::ERR_BROKEN_LINK,
                'message' => 'The link you\'re using is broken'
            ];
        }

        $resetPassword = $em->find(PasswordResets::class, ['email' => $email, 'token' => $token]);
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

        $user = $em->getRepository(User::class)->findOneByEmail($email);
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