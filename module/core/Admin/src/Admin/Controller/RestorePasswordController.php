<?php

namespace Admin\Controller;

use Veniva\Lbs\Adapters\Zend\SendMail;
use Veniva\Lbs\Adapters\Zend\Translator;
use Logic\Core\Admin\Authenticate\RestorePassword;
use Logic\Core\Admin\Form\RestorePasswordForm;
use Veniva\Lbs\Adapters\Zend\Http\Request;
use Veniva\Lbs\Interfaces\StatusCodes;
use Logic\Core\Stdlib\Strings;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Application\ServiceLocatorAwareTrait;
use Interop\Container\ContainerInterface;

class RestorePasswordController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;
    
    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function forgottenAction()
    {
        $restorePassword = new RestorePassword(new RestorePasswordForm(), new Translator($this->getTranslator()));
        $request = $this->getRequest();
        if($request->isPost()){
            $entityManager = $this->getServiceLocator()->get('entity-manager');
            $result = $restorePassword->postAction(iterator_to_array($request->getPost()), $entityManager);

            if($result->status === RestorePassword::ERR_NOT_FOUND){
                $this->flashMessenger()->addErrorMessage($result->message);
                return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));

            }
            else if($result->status === StatusCodes::ERR_INVALID_FORM){
                return array('form' => $result->get('form'));
            }
            else if($result->status === RestorePassword::ERR_NOT_ALLOWED){
                $this->flashMessenger()->addErrorMessage(sprintf($result->message, $result->get('email')));
                return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));
            }

            //generate email data
            $token = Strings::randomString(10);
            $uri = $this->getRequest()->getUri();
            $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
            $basePath = $renderer->basePath('/admin/resetpassword/reset');
            $baseUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
            $link = $baseUrl . $basePath. '?email=' . urlencode($result->email) . '&token=' . $token;
            $message = sprintf($this->translator->translate("Dear user,%sFollowing the new password request, here is a link for you to visit in order to create a new password:%s%s"), "\n\n", "\n\n", $link);

            $config = $this->getServiceLocator()->get('config');
            $data = [
                'email' => $result->get('email'),
                'token' => $token,
                'no-reply' => $config['other']['no-reply'],
                'message' => $message,
                'subject' => 'New password'
            ];

            $result = $restorePassword->persistAndSendEmail($entityManager, new SendMail(), $data);
            if($result->status === RestorePassword::ERR_SEND_MAIL){
                $this->flashMessenger()->addErrorMessage($result->message);
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
            }
            
            $this->flashMessenger()->addSuccessMessage(sprintf($result->message, $result->get('email')));
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
        }

        $form = $restorePassword->getAction();
        return array('form' => $form);
    }
}