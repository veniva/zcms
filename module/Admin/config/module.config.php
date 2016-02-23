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
                            'route'    => '/[:lang/][:controller[/:action][/:page[/:id]]]',
                            'constraints' => array(
                                'lang'          => '[a-zA-Z]{2}',
                                'controller'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'page'          => '[0-9]*',
                                'id'            => '[0-9]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Admin\Controller',
                                'controller'    => 'index',
                                'action'        => 'index',
                                'page'          => 1,
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
                                'id'            => 0,
                                'page'          => 1,
                            ),
                        ),
                    ),
                    'listing' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:lang]/listing[/:action][/:id[/:page[/:filter]]]',
                            'constraints' => array(
                                'lang'      => '[a-zA-Z]{2}',
                                'action'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'        => '[0-9]*',
                                'page'      => '[0-9]*',
                                'filter'      => '[0-9]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Admin\Controller',
                                'controller'    => 'listing',
                                'action'        => 'list',
                                'id'            => 0,
                                'page'          => 1,
                                'filter'        => 0,
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'category-tree' => 'Admin\Service\Factory\CategoryTree',
            'flag-codes' => 'Admin\Service\Factory\FlagCodes'
        ),
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
            'Admin\Controller\Category' => 'Admin\Controller\CategoryController',
            'Admin\Controller\Listing' => 'Admin\Controller\ListingController',
            'Admin\Controller\Language' => 'Admin\Controller\LanguageController',
            'Admin\Controller\User' => 'Admin\Controller\UserController',
            'Admin\Controller\Log' => 'Admin\Controller\LogController',
        ),
        'initializers' => array(
            'Admin\Controller\Initializer\Translator',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'template_map' => array(
            'admin/layout'              => __DIR__ . '/../view/layout/layout.phtml',
            'admin/login'               => __DIR__ . '/../view/layout/login.phtml',
            'admin/index/index'         => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404'                 => __DIR__ . '/../view/error/404.phtml',
            'error/index'               => __DIR__ . '/../view/error/index.phtml',
            'paginator/sliding'         => __DIR__ . '/../view/paginator/sliding.phtml',
            'helper/breadcrumb_admin'   => __DIR__ . '/../view/helper/breadcrumb.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'acl' => array(
        'resource' => array(
            'log' => null,
            'administrators' => null,
            'index' => null,
        ),
        'allow' => array(
            array('guest', 'log', array('in', 'forgotten', 'initial', 'reset')),
            array('admin', null, null),
            array('super-admin', null, null),
        ),
        'deny' => array(
            array('admin', 'administrators', null),
        ),
    ),
    'listing' => array(
        'img-path' => '/img/listing_img/'
    ),
);
