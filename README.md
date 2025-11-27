# wp-unit-tests
Tests setup and boilerplate for testing WordPress with PHPUnit.

## Usage in projects
To start using PHPUnit for unit testing in WordPress first install PHPUnit in your project (prefereably with Composer: `composer require --dev phpunit/phpunit`, see https://docs.phpunit.de/en/12.4/installation.html#composer).

Then navigate to your project root and run the following commands:
1. `composer require --dev tn/wp-unit-tests-scaffold`
2. `vendor/bin/scaffold`

We are assuming here, that your project root is a WordPress theme or a plugin. If the root is a different folder, then after installation adjust `tests/bootstrap.php` accordingly to point to a proper `wp-load.php` location.
