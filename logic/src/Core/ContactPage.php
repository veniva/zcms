<?php

namespace Logic\Core;

use Logic\Core\Adapters\Interfaces\ISendMail;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Form\Contact;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Doctrine\ORM\EntityManager;
use Zend\Mail;//v_todo - replace with better

class ContactPage
{
    const ERR_NO_ADMIN = 'cpage.no_admin';
    const SHOW_FORM = 'cpage.show';
    
    /** @var EntityManager */
    protected $em;
    
    /** @var Contact */
    protected $form;
    
    /** @var ISendMail */
    protected $sendMail;
    
    function __construct(EntityManager $em, Contact $form, ISendMail $sendMail)
    {
        $this->em = $em;
        $this->form = $form;
        $this->sendMail = $sendMail;
    }
    
    public function showPage()
    {
        $superAdmin = $this->em->getRepository(User::class)->findOneByRole(User::USER_SUPER_ADMIN);
        if(!$superAdmin){
            return [
                'status' => self::ERR_NO_ADMIN
            ];
        }
        
        return [
            'status' => self::SHOW_FORM
        ];
    }
    
    public function processForm(ITranslator $translator, array $postData):array
    {
        $superAdmin = $this->em->getRepository(User::class)->findOneByRole(User::USER_SUPER_ADMIN);
        if(!$superAdmin){
            return [
                'status' => self::ERR_NO_ADMIN
            ];
        }
        
        $this->form->setData($postData);
        if($this->form->isValid()){
            $name = $this->form->get('name')->getValue();
            $inquiry = $this->form->get('inquiry')->getValue();
            
            $this->sendMail->send(
                $this->form->get('email')->getValue(), 
                $superAdmin->getEmail(),
                $translator->translate('Website Inquiry'),
                $this->formatBody($translator, $name, $inquiry),
                ['Content-Type' => 'text/plain; charset=UTF-8']
            );

            return [
                'status' => StatusCodes::SUCCESS,
                'success_message' => "The message has been successfully sent. We'll review it and will answer shortly"
            ];
        }

        return [
            'status' => StatusCodes::ERR_INVALID_FORM,
            'form_active' => $superAdmin->getEmail() ? true : false
        ];
    }

    public function formatBody(ITranslator $translator, $name, $inquiry)
    {
        return $translator->translate('From').' '.$name.":\n\n".$inquiry;
    }
}