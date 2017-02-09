<?php

namespace Admin\Controller;


use Logic\Core\Admin\Authenticate\ResetPassword;
use Logic\Core\Admin\Interfaces\Authenticate\IResetPassword;
use Logic\Core\Interfaces\StatusCodes;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResetPasswordController extends AbstractActionController implements TranslatorAwareInterface
{
    use ServiceLocatorAwareTrait, TranslatorAwareTrait;

    /** @var  IResetPassword */
    protected $resetPassword;

    public function __construct(ServiceLocatorInterface $serviceLocator, IResetPassword $resetPassword)
    {
        $this->setServiceLocator($serviceLocator);
        $this->resetPassword = $resetPassword;
    }

    public function resetAction()
    {
        if($this->getRequest()->isPost()){
            $result = $this->resetPassword->resetPost();
            if($result['status'] === StatusCodes::SUCCESS){
                $this->flashMessenger()->addSuccessMessage($this->getTranslator()->translate($result['message']));
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
            }

        }else{
            $result = $this->resetPassword->resetGet();
        }

        if(($result['status'] !== StatusCodes::SUCCESS) && ($result['status'] !== ResetPassword::ERR_INVALID_FORM)) {
            $this->flashMessenger()->addErrorMessage($this->translator->translate($result['message']));
            return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));
        }

        return [
            'form' => $result['form']
        ];
    }
}