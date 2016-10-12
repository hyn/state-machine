# State machine

The state machine is a flexible library that helps you move Eloquent models 
from States through Transitions while emitting events along the way. 

## Example

Let's say we have a [Cat](tests/stubs/Models/Cat.php), who likes two things:

- [Sleeping](tests/stubs/States/Cat/Sleeping.php) and being
- [Awake](tests/stubs/States/Cat/Awake.php)

## Installation

```bash
composer require hyn/state-machine
```

Read more about installation and configuration on [state-machine.readme.io](https://state-machine.readme.io/docs).

## Tests

Run the tests with:

```bash
vendor/bin/phpunit
```
