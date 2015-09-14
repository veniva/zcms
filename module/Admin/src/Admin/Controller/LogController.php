<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 11/09/2015
 * Time: 16:02
 */

namespace Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;

class LogController extends AbstractActionController
{
    public function indexAction()
    {
        $this->redirect()->toRoute('admin/default', ['controller' => 'log', 'action' => 'in']);
    }

    public function inAction()
    {
        //V_TODO - configure translation folder and files for this module
        return array();
    }
}
