<?php

namespace Logic\Core;


use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Logic\Core\Form\Contact;
use Logic\Core\Model\Entity\User;
use Doctrine\ORM\EntityManager;
use Zend\Mail;//v_todo - replace with better

class ContactPage
{
    protected $em;
    protected $form;
    protected $request;
    
    function __construct(EntityManager $em, Contact $form, IRequest $request)
    {
        $this->em = $em;
        $this->form = $form;
        $this->request = $request;
    }
    
    public function process():array
    {
        $superAdmin = $this->em->getRepository(User::class)->findOneByRole(User::USER_SUPER_ADMIN);
        if($this->request->isPost()){
            $this->form->setData($this->request->getPost());
            if($this->form->isValid()){
                $message = new Mail\Message();
                $message->setFrom($this->form->get('email')->getValue())
                    ->setTo($superAdmin->getEmail())
                    ->setSubject('Website Inquiry')
                    ->setBody($this->form->get('inquiry')->getValue());

                $transport = new Mail\Transport\Sendmail();
                $transport->send($message);

                return [
                    'form_sent' => true,
                    'success_message' => "The message has been successfully sent. We'll review it and will answer shortly"
                ];
            }
        }

        return [
            'form_sent' => false,
            'form_active' => $superAdmin->getEmail() ? true : false
        ];
    }
}