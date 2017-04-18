<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Application\Controller;


use Logic\Core\Category;
use Logic\Core\Interfaces\StatusCodes;
use Zend\Mvc\Controller\AbstractActionController;
use Interop\Container\ContainerInterface;
use Zend\View\Model\ViewModel;
use Application\ServiceLocatorAwareTrait;

class CategoryController extends AbstractActionController
{
    use ServiceLocatorAwareTrait;

    /**
     * @var ContainerInterface
     */
    protected $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }
    
    public function showAction()
    {
        $alias = $this->params()->fromRoute('alias');

        $categoryLogic = new Category($this->serviceLocator->get('entity-manager'), $this->getServiceLocator()->get('language'));
        $data = $categoryLogic->process($alias);

        if($data['status'] !== StatusCodes::SUCCESS){
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
