<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 16:43
 */

namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CategoryController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function showAction()
    {
        return new ViewModel();
    }
}