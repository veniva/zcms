<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;


use Logic\Core\Adapters\Zend\Http\Request;
use Logic\Core\Admin;
use Zend\Form\Element;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Validator;
use Zend\Mail;

class LogController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    public function indexAction()
    {
        return $this->redir()->toRoute('admin/default', ['controller' => 'log', 'action' => 'in']);
    }

    public function inAction()
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $auth = $this->getServiceLocator()->get('auth');
        $request = $this->getRequest();
        $lRequest = new Request($request);

        if(!$lRequest->isPost()){
            $data = Admin\Authenticate\Login::inGet($entityManager);
            if($data['error']){
                $this->flashMessenger()->addInfoMessage($this->translator->translate($data['message']));
                return $this->redir()->toRoute('admin/default', ['controller' => 'register', 'action' => 'register']);
            }
            
        }else{
            $data = Admin\Authenticate\Login::inPost($lRequest->getPost(), $auth);
            if($data['error'] === true){
                $this->flashMessenger()->addErrorMessage($this->translator->translate($data['message']));
                $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));

            }else{
                $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate($data['message']), $data['user']->getUname()));
                return $this->redir()->toRoute('admin/default', array('controller' => 'index'));
            }
        }

        return [
            'form' => $data['form']
        ];
    }

    public function outAction()
    {
        $auth = $this->getServiceLocator()->get('auth');
        Admin\Authenticate\Logout::logout($auth);
        $this->flashMessenger()->addSuccessMessage($this->translator->translate('You have been logged out successfully'));
        return $this->redir()->toRoute('admin/default');
    }
}
