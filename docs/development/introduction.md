**Development and customization**
---------------------------------


----------

**File architecture**

The file structure is organized in a way that the core files of the ZCMS are isolated in separate folders so that it can be 
easily upgraded to the latest compatible version.

    logic                     #here are located the files that contain all the business logic used in the application's modules
    root/module/core          #this is the core modules folder
    root/public_html/core     #it contains the client side files like CSS and JS

You have to keep the files contained in these folders untouched. Instead you can override the core modules by creating 
new modules in `root/modules` for example, and place your client side files in `root/public_html` folder

**Customizing the appearance**

If you need to customize the front end user area appearance of the website, then you can do that by creating new module, 
named lets say `root/module/ApplicationOverride`. This module will contain only the view files from `Application` module 
that you want to override plus the file `root/module/ApplicationOverride/Module.php`. It'll also contain 
a `root/module/ApplicationOverride/config/module.config.php` file, that will actually map the files to be overridden. 
It's content might look like the following:

    return array(
        'view_manager' => array(
            'template_map' => array(
                'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
                'application/index/index' => __DIR__ . '/../view/application-override/index/index.phtml',
            ),
            'template_path_stack' => array(
                __DIR__ . '/../view',
            ),
        ),
    );

Then you need to create or copy paste the corresponding view `.phtml` files respectively 
from `root/module/core/Application/view/layout/layout.phtml` to `root/module/ApplicationOverride/view/layout/layout.phtml` and 
from `root/module/core/Application/view/application/index/index.phtml` to `root/module/ApplicationOverride/view/application-override/index/index.phtml` 
and after that you may customize the newly created files according to your needs. You can update the CSS & JS file references 
in `root/module/ApplicationOverride/view/layout/layout.phtml` to point to your newly created client-side files in `root/public_html` folder.

**Extending the CMS**

In order to extend ZCMS you have to be experienced in the development of applications using Zend Framework 2 and Doctrine 2 ORM. 
The ZCMS file structure contains various modules located in the folder `root/module/core` and the most important 
are `Application`  containing the front-end (user area) files and `Admin` containing the administration area files. 
The JavaScript and CSS code is located in the `public_html/core` folder. It is recommended to consult and stick with 
the best practices described in the [Zend Framework's documentation](http://framework.zend.com/manual/current/en/index.html) 
and [Doctrine ORM's documentation](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html) while 
developing and changing the functionality.  
  
In order to add/extend/override some of the logic in `root/module/core/Application/Module.php` you can write some code in the newly created `root/module/ApplicationOverride/Module.php` and or in `ApplicationOverride/src/ApplicationOverride/Controller/YourController.php`. You have to register the new module in `root/config/application.config.php`, listing the new module after the module being overridden:

    return array(
        'modules' => array(
            'Application',
            'MainMenu',
            'Admin',
            'Languages',
            'AdminLanguages',
            'ApplicationOverride',
        ),
        'module_listener_options' => array(
            'module_paths' => array(
                './module/core',
                './module',
                './vendor',
            ),
            'config_glob_paths' => array(
                'config/autoload/{,*.}{global,local}.php',
            ),
        ),
    );