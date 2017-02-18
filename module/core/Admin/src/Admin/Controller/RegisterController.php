<?php

namespace Admin\Controller;


use Doctrine\ORM\EntityManager;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\User;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Logic\Core\Admin;
use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\Admin\Form\Register as RegisterForm;

class RegisterController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;
    
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }
    
    public function registerAction()
    {
        $serviceLocator = $this->getServiceLocator();
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get('entity-manager');
        /** @var User $user */
        $user = $serviceLocator->get('user-entity');
        $flagCodes = $serviceLocator->get('flag-codes')->getFlagCodeOptions();
        $register = new Admin\Authenticate\Register($entityManager, $user, new RegisterForm($user, $entityManager, $flagCodes));

        $request = new Request($this->getRequest());
        if($request->isPost()){
            $result = $register->postAction($request->getPost());
            if($result['status'] == StatusCodes::SUCCESS){
                $langCode = $result['lang_iso'];
                $locale = $locale = ($langCode != 'en') ? $langCode.'_'.strtoupper($langCode) : 'en_US';
                $this->flashMessenger()->addSuccessMessage($this->translator->translate($result['message'], 'default', $locale));
                $routeParams = ['controller' => 'log', 'action' => 'in'];
                if($langCode !== 'en'){
                    $routeParams['lang'] = $langCode;
                }
                return $this->redir()->toRoute('admin/default', $routeParams);
            }

        }else{
            $result = $register->getAction();
        }

        if($result['status'] == $register::ERR_USER_EXISTS){
            return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));
        }

        return [
            'form' => $result['form'],
            'flagCode' => $this->getRequest()->isPost() ? $this->params()->fromPost('isoCode') : null
        ];
    }
}