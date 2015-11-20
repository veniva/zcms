<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

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
                $this->redir()->toRoute('home/default', array('controller' => 'customPage', 'action' => 'contact'));
            }
        }

        return array(
            'formActive' => $adminEmail ? true : false,
            'contact_form' => $form
        );
    }
}
