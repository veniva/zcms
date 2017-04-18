<?php

namespace Admin\Controller;

use Logic\Core\Adapters\Zend\SendMail;
use Logic\Core\Adapters\Zend\Translator;
use Logic\Core\Admin\Authenticate\RestorePassword;
use Logic\Core\Admin\Form\RestorePasswordForm;
use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Stdlib\Strings;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Application\ServiceLocatorAwareTrait;
use Interop\Container\ContainerInterface;

class RestorePasswordController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /** @var RestorePassword  */
    protected $restorePassword;
    
    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function forgottenAction()
    {
        $restorePassword = new RestorePassword(new RestorePasswordForm(), new Translator($this->translator));
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $request = new Request($this->getRequest());
        if($request->isPost()){

            $result = $restorePassword->postAction($request->getPost(), $entityManager);

            if($result['status'] == RestorePassword::ERR_NOT_FOUND){
                $this->flashMessenger()->addErrorMessage($result['message']);
                return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));

            }
            else if($result['status'] == StatusCodes::ERR_INVALID_FORM){
                return array('form' => $result['form']);
            }
            else if($result['status'] == RestorePassword::ERR_NOT_ALLOWED){
                $this->flashMessenger()->addErrorMessage(sprintf($result['message']), $result['email']);
                return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));
            }

            //generate email data
            $token = Strings::randomString(10);
            $uri = $this->getRequest()->getUri();
            $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
            $basePath = $renderer->basePath('/admin/log/reset');
            $baseUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
            $link = $baseUrl . $basePath. '?email=' . urlencode($result['email']) . '&token=' . $token;
            $message = sprintf($this->translator->translate("Dear user,%sFollowing the new password request, here is a link for you to visit in order to create a new password:%s%s"), "\n\n", "\n\n", $link);

            $config = $this->getServiceLocator()->get('config');
            $data = [
                'email' => $result['email'],
                'token' => $token,
                'no-reply' => $config['other']['no-reply'],
                'message' => $message,
                'subject' => 'New password'
            ];

            $result = $this->restorePassword->persistAndSendEmail($entityManager, new SendMail(), $data);
            if($result['status'] === RestorePassword::ERR_SEND_MAIL){
                $this->flashMessenger()->addErrorMessage($result['message']);
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
            }
            
            $this->flashMessenger()->addSuccessMessage(sprintf($result['message']), $result['email']);
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
        }

        $form = $restorePassword->getAction();
        return array('form' => $form);
    }
}