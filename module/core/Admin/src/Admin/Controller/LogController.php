<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Veniva\Lbs\Adapters\Zend\Translator;
use Logic\Core\Admin;
use Veniva\Lbs\Interfaces\StatusCodes;
use Zend\Form\Element;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Application\ServiceLocatorAwareTrait;
use Interop\Container\ContainerInterface;
use Zend\Validator;
use Zend\Mail;

class LogController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
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

        $logic = new Admin\Authenticate\Login(new Translator($this->translator));
        
        if(!$request->isPost()){
            $result = $logic->inGet($entityManager);
            if($result->status !== StatusCodes::SUCCESS){
                $this->flashMessenger()->addInfoMessage($result->message);
                return $this->redir()->toRoute('admin/default', ['controller' => 'register', 'action' => 'register']);
            }
            
        }else{
            $result = $logic->inPost($auth, iterator_to_array($request->getPost()));
            if($result->status !== StatusCodes::SUCCESS){
                $this->flashMessenger()->addErrorMessage($result->message);
                return $this->redir()->toRoute('admin/default', array('controller' => 'log', 'action' => 'in'));

            }else{
                $this->flashMessenger()->addSuccessMessage(sprintf($result->message, $result->get('user')->getUname()));
                return $this->redir()->toRoute('admin/default', array('controller' => 'index'));
            }
        }

        return [
            'form' => $result->get('form')
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
