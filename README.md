# wp-unit-tests
Tests setup and boilerplate for testing WordPress with PHPUnit.

## Usage in projects
To start using PHPUnit for unit testing in WordPress first install PHPUnit in your project (prefereably with Composer: `composer require --dev phpunit/phpunit:^9.5`, see https://docs.phpunit.de/en/12.4/installation.html#composer). We need version 9.5 to be compatible with WordPress tests SDK.

Then install WordPress tests SDK with `composer require --dev wp-phpunit/wp-phpunit` and `composer require --dev yoast/phpunit-polyfills:"^2.0"`.

Then navigate to your project root and run the following commands:
1. `composer require --dev tn-xfive/wp-unit-tests-scaffold`
2. `vendor/bin/scaffold`

See `wp-tests-config-sample.php` file in `/tests` directory and edit it as instructed.

We are assuming here, that your project root is a WordPress theme or a plugin. If the root is a different folder, then after installation adjust `tests/bootstrap.php` accordingly to point to a proper `wp-load.php` location.

## Test file boilerplate
There is a boilerplate test file prefilled with some handy test case examples. You can copy them elsewhere and populate the file with your own tests.
