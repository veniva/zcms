# Unit Testing #


----------

ZCSM includes comprehensive unit tests based on the Zend framework's testing API using PHPUnit. It requires that a testing database is set in order to run the unit tests.

## Preparing the testing database ##

The configuration of the tests is located in `/module/core/Application/tests/config` folder. There you have to copy 
the `database.local.php.dist` into  `database.local.php` and edit the file providing the name of an empty database MySQL database, 
or if you're using different type of database then copy and modify the appropriate code from the previously 
defined DB in `root/config/autoload/database.local.php`.
In order to populate the DB schema open a terminal, scroll to `/module/core/Application/tests/` and run:

    ../../../../vendor/bin/doctrine orm:schema-tool:create

This will use the file `/module/core/Application/tests/cli-config.php` as an entry point to use the configuration specific for the tests

## Running the tests ##

To run the tests, open a terminal window, scroll to the project root and run the command:

    vendor/bin/phpunit -c module/core/Application/tests/phpunit.xml

This will use the test configuration file located at `/module/core/Application/tests/phpunit.xml`.  
  
For more specific commands on how to run separate tests please visit the [PHPUnit's documentation](https://phpunit.de/manual/current/en/textui.html)