# state machine

This package allows you to easily set up a state machine. A few strategies which deserve my focus:

- As flexible as possible.
- Event driven.

## Installation

```bash
composer require hyn/state-machine
```

In case you're using Laravel, register the service provider to make use of the State machine processor.

For this to work change the `config/app.php` and add an entry under `providers`:

```php
    'providers' => [
        ...
        Hyn\Statemachine\Providers\StatemachineProvider::class,
    ];
```

## Tests

Run the tests with:

```bash
vendor/bin/phpunit
```