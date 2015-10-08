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
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route'    => '/[:lang]',
                    'constraints' => array(
                        'lang'    => '[a-zA-Z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                        'lang'       => 'en',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/app',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                        'lang'          => 'en',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:lang/][:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'lang'       => '[a-zA-Z]{2}',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            //http://framework.zend.com/manual/current/en/modules/zend.mvc.routing.html
            'category' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/[:lang/]category[/][:alias]',
                    'constraints' => array(
                        'alias'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'lang'     => '[a-zA-Z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Category',
                        'action' => 'show',
                        'alias' => 'home',
                        'lang' => 'en',
                    ),
                ),
            ),
            'page' => array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/[:lang/]page[/][:alias]',
                    'constraints' => array(
                        'alias'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'lang'     => '[a-zA-Z]{2}',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Page',
                        'action' => 'show',
                        'lang' => 'en',
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
        ),
        'invokables' => array(
            'misc' => 'Application\Service\Invokable\Misc',
            'listing-entity' => 'Application\Model\Entity\Listing',
            'listing-content-entity' => 'Application\Model\Entity\ListingContent',
            'category-entity' => 'Application\Model\Entity\Category',
            'category-content-entity' => 'Application\Model\Entity\CategoryContent',
            'category-relations-entity' => 'Application\Model\Entity\CategoryRelations',
            'user-entity' => 'Application\Model\Entity\User',
            'lang-entity' => 'Application\Model\Entity\Lang',
            'auth-adapter' => 'Application\Authentication\Adapter',
        ),
        'shared' => array(
            'user-entity' => false,
        ),
        'initializers' => array(
            'Application\Service\Initializer\Password',
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
            'Application\Controller\Category' => 'Application\Controller\CategoryController',
            'Application\Controller\Page' => 'Application\Controller\PageController',
            'Application\Controller\CustomPage' => 'Application\Controller\CustomPageController',
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
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
//            'langUrl' => 'Application\View\Helper\Url',
        ),
    ),
    'doctrine' => array(
        'entity_path' => array(
            __DIR__.'/../src/Application/Model/Entity'
        ),
        'initializers' => array(
            'Application\Service\Initializer\Password'
        )
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
            'role' => 'guest'
        ),
        'modules' => array(
            'Application', 'Admin'
        ),
    ),
);
