<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '[:lang/][:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'lang'       => '[a-zA-Z]{2}',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Application\Controller'
                            ),
                        ),
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            //http://framework.zend.com/manual/current/en/modules/zend.mvc.routing.html
            'category' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/[:lang/]category[/][:alias]',
                    'constraints' => array(
//                        'alias'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'lang'     => '[a-zA-Z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Category',
                        'action' => 'show',
                        'alias' => 'home',
                    ),
                ),
            ),
            'page' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/[:lang/]page[/][:alias]',
                    'constraints' => array(
                        //'alias'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'lang'     => '[a-zA-Z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Page',
                        'action' => 'show',
                    ),
                ),
            ),

        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'dbadapter' => 'Application\Service\Factory\DbAdapter',
            'entity-manager' => 'Application\Service\Factory\EntityManager',
            'password-adapter' => 'Application\Service\Factory\PasswordAdapter',
            'auth' => 'Application\Service\Factory\Authentication',
            'current-user' => 'Application\Service\Factory\CurrentUser',
            'acl' => 'Application\Service\Factory\Acl',
            'validator-messages' => 'Application\Service\Factory\ValidatorMessages',
            'language' => 'Application\Service\Factory\Language',
            'auth-adapter' => 'Application\Service\Factory\AuthenticationAdapterFactory',
        ),
        'invokables' => array(
            'misc' => 'Application\Service\Invokable\Misc',
            'listing-entity' => 'Application\Model\Entity\Listing',
            'listing-content-entity' => 'Application\Model\Entity\ListingContent',
            'category-entity' => 'Application\Model\Entity\Category',
            'category-content-entity' => 'Application\Model\Entity\CategoryContent',
            'user-entity' => 'Application\Model\Entity\User',
            'lang-entity' => 'Application\Model\Entity\Lang',
            'stdlib-file-system' => 'Application\Stdlib\FileSystem',
            'stdlib-strings' => 'Application\Stdlib\Strings',
        ),
        'initializers' => array(
            'Application\Service\Initializer\Password',
        ),
        'shared' => array(
            'listing-entity' => false,
            'listing-content-entity' => false,
            'category-entity' => false,
            'category-content-entity' => false,
            'user-entity' => false,
            'lang-entity' => false,
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
            'Application\Controller\Index' => 'Application\Controller\IndexController',
        ),
        'factories' => array(
            'Application\Controller\Page' => 'Application\Controller\Factory\PageControllerFactory',
            'Application\Controller\Category' => 'Application\Controller\Factory\CategoryControllerFactory',
            'Application\Controller\CustomPage' => 'Application\Controller\Factory\CustomPageControllerFactory',
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'redir' => 'Application\Controller\Plugin\Redir',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/blank'           => __DIR__ . '/../view/layout/blank.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'helper/breadcrumb'       => __DIR__ . '/../view/helper/breadcrumb.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'factories' => array(
            'breadcrumb' => 'Application\View\Helper\Factory\Breadcrumb',
        ),
    ),
    'doctrine' => array(
        'entity_path' => array(
            __DIR__.'/../src/Application/Model/Entity'
        ),
        'initializers' => array(
            'Application\Service\Initializer\Password'
        ),
        'proxy_dir' => __DIR__.'/../../../data/doctrine-proxy',
        'is_dev_mode' => false,
    ),
    'other' => array(
        'no-reply' => !empty($_SERVER['SERVER_NAME']) ? 'no-reply@'.$_SERVER['SERVER_NAME'] : '',
    ),
    'acl' => array(
        'role' => array(
            'guest' => null,
            'user' => 'guest',
            'admin' => 'user',
            'super-admin' => null,
        ),
        'resource' => array(),
        'resource_aliases' => array(),
        'allow' => array(),
        'deny' => array(),
        'defaults' => array(
            'role' => array(
                'guest' => 'guest',
                'admin' => 'admin'
            )
        ),
        'modules' => array(
            'Admin'
        ),
    ),
    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format'      => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
            'message_close_string'     => '</li></ul></div>',
            'message_separator_string' => '</li><li>'
        )
    )
);
