<?php

namespace Admin\Controller;


use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\Adapters\Zend\Translator;
use Logic\Core\Admin\Authenticate\ResetPassword;
use Logic\Core\Admin\Form\ResetPassword as ResetPasswordForm;
use Logic\Core\Interfaces\StatusCodes;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorInterface;

class ResetPasswordController extends AbstractActionController implements TranslatorAwareInterface
{
    use ServiceLocatorAwareTrait, TranslatorAwareTrait;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function resetAction()
    {
        $request = new Request($this->getRequest());
        $email = $request->getQuery('email');
        $token = $request->getQuery('token');
        /** @var EntityManager $em */
        $em = $this->getServiceLocator()->get('entity-manager');
        $logic = new ResetPassword($em, new ResetPasswordForm(), new Translator($this->translator), $email, $token);
        if($request->isPost()){
            $result = $logic->resetPost($request->getPost());
            if($result['status'] === StatusCodes::SUCCESS){
                $this->flashMessenger()->addSuccessMessage($result['message']);
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
            }

        }else{
            $result = $logic->resetGet();
        }

        if(($result['status'] !== StatusCodes::SUCCESS) && ($result['status'] !== StatusCodes::ERR_INVALID_FORM)) {
            $this->flashMessenger()->addErrorMessage($result['message']);
            return $this->redir()->toRoute('admin/default', array('controller' => 'restorepassword', 'action' => 'forgotten'));
        }

        return [
            'form' => $result['form']
        ];
    }
}