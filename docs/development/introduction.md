**Development and customization**
---------------------------------


----------


**Customizing the appearance**

If you need to customize the front end user area appearance of the website, then you need to go to `module/Application/view/application` folder and edit the template files with extension .phtml located there. Those are normal html files mixed with PHP code. The CSS files and images are located in the `public_html` folder.

**Extending the CMS**

In order to extend ZCMS you have to be experienced in the development of applications using Zend Framework 2 and Doctrine 2 ORM. The ZCMS file structure contains various modules located in the folder `module` and the most important are `Appication`  containing the front-end (user area) files and `Admin` containing the administration area files. The JavaScript and CSS code is located in the `public_html/` folder. It is recommendable to consult and stick with the best practices described in the [Zend Framework's documentation](http://framework.zend.com/manual/current/en/index.html) and [Doctrine ORM's documentation](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/index.html) while developing and changing functionalities.