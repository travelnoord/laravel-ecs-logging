# Travelnoord Ecs Logging

This package implements Elastic ECS logging.

## Installation

To install this package, define the repository in your composer.json file, for example:

```
composer require travelnoord/laravel-ecs-logging
```

## Configuration

TBD

### Only filter out the log message

TBD

## Usage

### Filter Secrets

To filter out secrets or other private information call the log filter:

```php
\Ecs::secret('my-secret-password');
```

or

```php
\Travelnoord\Logging\Facades\Ecs::secret('my-secret-password');
```

You can also pass an array with credentials:

```php
\Ecs::filter([
    'api-key-1',
    'api-key-2',
])
```

### Log Context
 TBD

## Development

Several checkers are available by default if this package is installed with dev-dependencies enabled.

### Composer Checks

To run all the checks:

```shell
composer ci
```

To run all static checks:

```shell
composer lint
```

To run only tests:

```shell
composer test
```

### PHPUnit

You can run PHPUnit by simply running `vendor/bin/phpunit` in the directory the package is installed in. It will
automatically detect the phpunit configuration file for the project, and detect which directory it should run in.

### PHPStan

You can run PHPStan by simply running `vendor/bin/phpstan` in the directory the package is installed in. It will
automatically detect the phpstan configuration file for the project, and detect which directory it should run in.

### PHPCS

You may run PHPCS by running `vendor/bin/phpcs src/` in the directory the package is installed in. It will use the
phpcs.xml file defined in the project root directory to determine which coding standards to use.
