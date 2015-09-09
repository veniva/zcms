<?php

namespace Application\Controller;


use Application\Form\Contact;
use Application\Service\Invokable\Misc;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mail;

class CustomPageController extends AbstractActionController
{
    public function contactAction()
    {
        $adminEmail = Misc::getAdminEmail();
        $form = new Contact();

        $request = $this->getRequest();
        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $message = new Mail\Message();
                $message->setFrom($form->get('email')->getValue())
                        ->setTo($adminEmail)
                        ->setBody($form->get('inquiry')->getValue());

                $transport = new Mail\Transport\Sendmail();
                $transport->send($message);

                $this->flashMessenger()->addSuccessMessage("The message has been successfully sent. We'll review it and will answer shortly");
                $this->redirect()->toRoute('application/default', array('controller' => 'customPage', 'action' => 'contact'));
            }
        }

        return array('contact_form' => $form);
    }
}
