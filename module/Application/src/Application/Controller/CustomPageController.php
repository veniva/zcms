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
        $request = $this->getRequest();
        $publicHtml = $this->getServiceLocator()->get('config')['public-path'];
        $form = new Contact($request->getBaseUrl().'/img/captcha/', $publicHtml);

        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $message = new Mail\Message();
                $message->setFrom($form->get('email')->getValue())
                        ->setTo($adminEmail)
                        ->setSubject('Website Inquiry')
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
