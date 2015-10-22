<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Admin\Controller;
return array(
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route'    => '/[:lang/][:controller[/:action][/:id]]',
                            'constraints' => array(
                                'lang'          => '[a-zA-Z]{2}',
                                'controller'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'            => '[0-9]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Admin\Controller',
                                'controller'    => 'index',
                                'action'        => 'index',
                                'lang'          => 'en',
                            ),
                        ),
                    ),
                    'category' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:lang]/category[/:action][/:id[/:page]]',
                            'constraints' => array(
                                'lang'      => '[a-zA-Z]{2}',
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'        => '[0-9]*',
                                'page'      => '[0-9]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Admin\Controller',
                                'controller'    => 'category',
                                'action'        => 'list',
                                'lang'          => 'en',
                                'id'            => 0,
                                'page'          => 1,
                            ),
                        ),
                    ),
                    'listing' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:lang]/listing[/:action][/:id[/:page]]',
                            'constraints' => array(
                                'lang'      => '[a-zA-Z]{2}',
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'        => '[0-9]*',
                                'page'      => '[0-9]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Admin\Controller',
                                'controller'    => 'listing',
                                'action'        => 'list',
                                'lang'          => 'en',
                                'id'            => 0,
                                'page'          => 1,
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
        ),
        'factories' => array(
            'Admin\Controller\Log' => function(ServiceLocatorAwareInterface $sm){
                $translator = $sm->getServiceLocator()->get('translator');
                return new Controller\LogController($translator);
            },
            'Admin\Controller\Category' => function(ServiceLocatorAwareInterface $sm){
                $translator = $sm->getServiceLocator()->get('translator');
                return new Controller\CategoryController($translator);
            },
            'Admin\Controller\Listing' => function(ServiceLocatorAwareInterface $sm){
                $translator = $sm->getServiceLocator()->get('translator');
                return new Controller\ListingController($translator);
            },
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
            'admin/layout'       => __DIR__ . '/../view/layout/layout.phtml',
            'admin/login'        => __DIR__ . '/../view/layout/login.phtml',
            'admin/index/index'  => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404'          => __DIR__ . '/../view/error/404.phtml',
            'error/index'        => __DIR__ . '/../view/error/index.phtml',
            'paginator/category_sliding'  => __DIR__ . '/../view/paginator/category_sliding.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'acl' => array(
        'resource' => array(
            'log' => null,
            'administrators' => null,
            'index' => null,
        ),
        'allow' => array(
            array('guest', 'log', array('in', 'forgotten')),
            array('admin', null, null),
            array('super-admin', null, null),
        ),
        'deny' => array(
            array('admin', 'administrators', null),
        ),
    ),
);
