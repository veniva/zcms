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
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Index',
                        'action'     => 'index',
                        'page_cache' => false,
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
                        ),
                    ),
                ),
            ),
            'category' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/[:lang/]category[/][:alias]',
                    'constraints' => array(
//                        'alias'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'lang'     => '[a-zA-Z]{2}',
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Category',
                        'action' => 'show',
                        'alias' => 'home',
                        'page_cache' => false,
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
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Page',
                        'action' => 'show',
                        'action_cache' => false
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
            'text-cache' => 'Zend\Cache\Service\StorageCacheFactory',
            'send-mail' => 'Application\Service\Factory\SendMailFactory',
        ),
        'invokables' => array(
            'misc' => 'Application\Service\Invokable\Misc',
            'listing-entity' => 'Logic\Core\Model\Entity\Listing',
            'listing-content-entity' => 'Logic\Core\Model\Entity\ListingContent',
            'category-entity' => 'Logic\Core\Model\Entity\Category',
            'category-content-entity' => 'Logic\Core\Model\Entity\CategoryContent',
            'user-entity' => 'Logic\Core\Model\Entity\User',
            'lang-entity' => 'Logic\Core\Model\Entity\Lang',
            'stdlib-file-system' => 'Application\Stdlib\FileSystem',
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
    'session' => array(
        'use_cookies' => true,
        'cookie_httponly' => true,
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
            'layout/blank'            => __DIR__ . '/../view/layout/blank.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/403'               => __DIR__ . '/../view/error/403.phtml',
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
        'invokables' => array(
            'corePath' => 'Application\View\Helper\CorePath',
        ),
    ),
    'doctrine' => array(
        'entity_path' => array(
            dirname(__DIR__, 3).'/logic/Core/Model/Entity',
        ),
        'initializers' => array(
            'Application\Service\Initializer\Password'
        ),
        'proxy_dir' => __DIR__.'/../../../../data/doctrine-proxy',
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
        'modules' => array(),
    ),
    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format'      => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
            'message_close_string'     => '</li></ul></div>',
            'message_separator_string' => '</li><li>'
        )
    ),
    'public-path' => __DIR__.'/../../../../public_html/core/',
    'listing' => array(
        'img-core-dir' => __DIR__.'/../../../../public_html/core/img/listing_img/'
    ),
);
