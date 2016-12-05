# State machine

The state machine is a flexible library that helps you move Eloquent models 
from States through Transitions while emitting events along the way. 

## Example

Let's say we have a [Cat](tests/stubs/Models/Cat.php), who does two things, being

- [Asleep](tests/stubs/States/Cat/Asleep.php) and
- [Awake](tests/stubs/States/Cat/Awake.php)

In order to become Awake from his initial state being Asleep, the cat has to

- [WakeUp](tests/stubs/Transitions/Cat/WakesUp.php)

Now if we look at the [state machine definition](tests/stubs/Definitions/CatDefinition.php) we can see 
the above is perfectly mapped out.

## Installation

```bash
composer require hyn/state-machine
```

__Read more__ about installation and configuration on [state-machine.readme.io](https://state-machine.readme.io/docs).

## Tests

Run the tests with:

```bash
vendor/bin/phpunit
```
