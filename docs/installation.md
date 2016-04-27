To install a copy on your localhost is quite straight forward.
  
**Requirements**  
 
PHP 5.5+  
Web server: all [handled byZF2](http://framework.zend.com/manual/current/en/ref/installation.html#web-server-setup), incl. Apache Nginx and more  
Database: all [handled by Doctrine 2](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver), incl. MySQL, PostgreSQL, SQLite and more 
  
**Installation**  
 
1. Download or using composer run: `php composer.phar create-project veniva/zcms [my-app-name]`
replacing the [my-app-name] with a name of a directory, and go to step 4  
2. Put under the server root  
3. Scroll to the zcms's root folder and run `composer update`  
4. Scroll to config/autoload and rename database.local.php.dist into database.local.php  
Note: it contains three sample configurations. The second and third are respectively MySQL and PgSQL which are commented out. The first one that will be in use is using an SQLite database.  
5. Setup a DB - do one of the following:  
    - SQLite (good for quick demo) - copy `install/zcms.sqlite` to `data/zcms.sqlite`  
    - MySQL - dump the `install/mysql.sql` data into your database and set the configuration in `config/autoload/database.local.php`  
    - PostgreSQL - restore the backup located at `install/PostgreSQL.backup` and set the configuration in `config/autoload/database.local.php`

Alternatively, you can use the Doctrine's SchemaTool to generate the database schema for you:

    cd project/
    $ vendor/bin/doctrine orm:schema-tool:create


To find more documentation on this tool [click here](http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html#generating-the-database-schema).  
6. Copy `config/application.config.php.dist` to `config/application.config.php`  
7. Copy `/config/autoload/crypt.local.php.dist` to `/config/autoload/crypt.local.php` and make the necessary changes.
It is recommended that you specify custom 'salt' and 'cost' for the bcrypt algorithm used for password encryption.   

Now visit the public_html folder of the application via the browser. This is the front end which is currently empty.
Visit the public_html/admin to setup an account for the administration panel and enter some data