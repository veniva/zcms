<?php

namespace Admin\Controller;

use Logic\Core\Admin\Authenticate\RestorePassword;
use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\Admin\Interfaces\Authenticate\IRestorePassword;
use Logic\Core\Stdlib\Strings;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;

class RestorePasswordController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /** @var RestorePassword  */
    protected $restorePassword;
    
    public function __construct(ServiceLocatorInterface $serviceLocator, IRestorePassword $restorePassword)
    {
        $this->setServiceLocator($serviceLocator);
        $this->restorePassword = $restorePassword;
    }

    public function forgottenAction()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $request = new Request($this->getRequest());
        if($request->isPost()){

            $result = $this->restorePassword->postAction($request->getPost(), $entityManager);

            if($result['status'] == RestorePassword::ERR_NOT_FOUND){
                $this->flashMessenger()->addErrorMessage($this->translator->translate($result['message']));
                return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));

            }
            else if($result['status'] == RestorePassword::ERR_INVALID_FORM){
                return array('form' => $result['form']);
            }
            else if($result['status'] == RestorePassword::ERR_NOT_ALLOWED){
                $this->flashMessenger()->addErrorMessage(sprintf($this->translator->translate($result['message']), $result['email']));
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
                'message' => $message
            ];

            $result = $this->restorePassword->persistAndSendEmail($entityManager, $this->getServiceLocator()->get('send-mail'), $data);

            $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate($result['message']), $result['email']));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
        }

        $form = $this->restorePassword->getAction();
        return array('form' => $form);
    }
}