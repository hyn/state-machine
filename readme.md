# State machine

The state machine is a flexible library that helps you move Eloquent models 
from States through Transitions while emitting events along the way. 

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
