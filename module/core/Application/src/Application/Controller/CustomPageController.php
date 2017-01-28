<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;


use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\ContactPage;
use Logic\Core\Form\Contact;
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
        $request = $this->getRequest();
        $publicHtml = $this->getServiceLocator()->get('config')['public-path'];
        $form = new Contact($request->getBaseUrl().'/core/img/captcha/', $publicHtml);
        $entityManager = $this->getServiceLocator()->get('entity-manager');

        $contactPageLogic = new ContactPage($entityManager, $form, new Request($request));
        $data = $contactPageLogic->process();
        if($data['form_sent']){
            $this->flashMessenger()->addSuccessMessage($data['success_message']);
            return $this->redir()->toRoute('home/default', array('controller' => 'customPage', 'action' => 'contact'));
        }

        return array(
            'formActive' => $data['form_active'],
            'contact_form' => $form
        );
    }
}
