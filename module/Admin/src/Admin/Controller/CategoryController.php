<?php

namespace Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;

class CategoryController extends AbstractActionController
{
    public function indexAction()
    {

        return ['title' => 'Categories'];
    }
}