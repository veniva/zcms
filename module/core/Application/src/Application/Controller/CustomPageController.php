<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;


use Application\Form\Contact;
use Application\Model\Entity\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mail;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomPageController extends AbstractActionController
{
    use ServiceLocatorAwareTrait;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }
    
    public function contactAction()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $superAdmin = $entityManager->getRepository(get_class(new User()))->findOneByRole(User::USER_SUPER_ADMIN);

        $request = $this->getRequest();
        $publicHtml = $this->getServiceLocator()->get('config')['public-path'];
        $form = new Contact($request->getBaseUrl().'/core/img/captcha/', $publicHtml);

        if($request->isPost()){
            $form->setData($request->getPost());
            if($form->isValid()){
                $message = new Mail\Message();
                $message->setFrom($form->get('email')->getValue())
                        ->setTo($superAdmin->getEmail())
                        ->setSubject('Website Inquiry')
                        ->setBody($form->get('inquiry')->getValue());

                $transport = new Mail\Transport\Sendmail();
                $transport->send($message);

                $this->flashMessenger()->addSuccessMessage("The message has been successfully sent. We'll review it and will answer shortly");
                $this->redir()->toRoute('home/default', array('controller' => 'customPage', 'action' => 'contact'));
            }
        }

        return array(
            'formActive' => $superAdmin->getEmail() ? true : false,
            'contact_form' => $form
        );
    }
}
