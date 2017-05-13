<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;

use Veniva\Lbs\Adapters\Zend\Http\Request as LogicRequest;
use Veniva\Lbs\Adapters\Zend\SendMail;
use Veniva\Lbs\Adapters\Zend\Translator;
use Logic\Core\ContactPage;
use Logic\Core\Form\Contact;
use Veniva\Lbs\Interfaces\StatusCodes;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mail;
use Interop\Container\ContainerInterface;
use Application\ServiceLocatorAwareTrait;

class CustomPageController extends AbstractActionController implements TranslatorAwareInterface
{
    use ServiceLocatorAwareTrait, TranslatorAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }
    
    public function contactAction()
    {
        $request = $this->getRequest();
        $publicHtml = $this->getServiceLocator()->get('config')['public-path'];
        $form = new Contact($request->getBaseUrl().'/core/img/captcha/', $publicHtml);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $logicRequest = new LogicRequest($request);
        $contactPageLogic = new ContactPage($entityManager, $form, new SendMail());

        if($request->isPost()){
            $data = $contactPageLogic->processForm(new Translator($this->translator), $logicRequest->getPost());

            if($data['status'] == StatusCodes::SUCCESS){
                $this->flashMessenger()->addSuccessMessage($this->translator->translate($data['success_message']));
                return $this->redir()->toRoute('home/default', array('controller' => 'custompage', 'action' => 'contact'));
            }

        }else{
            $data = $contactPageLogic->showPage();
        }

        return array(
            'formActive' => $data['status'] == ContactPage::ERR_NO_ADMIN ? false : true,
            'contact_form' => $form
        );
    }
}
