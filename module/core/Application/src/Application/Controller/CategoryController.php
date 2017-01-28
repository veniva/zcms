<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;


use Logic\Core\Category;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractActionController
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
    
    public function showAction()
    {
        $alias = $this->params()->fromRoute('alias');
        if(!$alias){
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $categoryLogic = new Category($this->serviceLocator->get('entity-manager'), $this->getServiceLocator()->get('language'));
        $data = $categoryLogic->process($alias);

        if($data['error']){
            $this->getResponse()->setStatusCode(404);
            return [];
        }

        $this->layout()->setVariables([
            'meta_title' => $data['category_content']->getTitle()
        ]);

        return new ViewModel([
            'category' => $data['category'],
            'category_content' => $data['category_content'],
            'sub_categories' => $data['sub_categories'],
            'langID' => $data['lang_id']
        ]);
    }
}
