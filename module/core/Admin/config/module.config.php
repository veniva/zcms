<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
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
                            'route'    => '/[:lang/][:controller[/:action]]',
                            'constraints' => array(
                                'lang'          => '[a-zA-Z]{2}',
                                'controller'    => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Admin\Controller',
                                'controller'    => 'index',
                                'action'        => 'index',
                            ),
                        ),
                    ),
                    'rest' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route'    => '[/:lang]/:controller[/:action][/:id]',
                            'constraints' => array(
                                'lang'          => '[a-zA-Z]{2}',
                                'controller'    => 'language|user|category|listing',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'            => '[0-9]*',
                            ),
                            'defaults' => array(
                                'action'        => null,
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
        ),
        'initializers' => array(
            'Admin\Controller\Initializer\Translator',
        ),
        'factories' => array(
            'Admin\Controller\Category' => 'Admin\Controller\Factory\CategoryControllerFactory',
            'Admin\Controller\Listing' => 'Admin\Controller\Factory\ListingControllerFactory',
            'Admin\Controller\Log' => 'Admin\Controller\Factory\LogControllerFactory',
            'Admin\Controller\Language' => 'Admin\Controller\Factory\LanguageControllerFactory',
            'Admin\Controller\User' => 'Admin\Controller\Factory\UserControllerFactory',
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
    'view_helpers' => array(
        'factories' => array(
            'admin_breadcrumb' => 'Admin\View\Helper\Factory\Breadcrumb',
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
        'modules' => array(
            'Admin'
        ),
        'admin_login' => array(
            'route' => 'admin/default',
            'controller' => 'Admin\Controller\Log',
            'action' => 'in',
        ),
    ),
);
