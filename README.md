# Mixtape

Model, Data Store, Data Transfer Object and REST API Controller Library for WordPress

## Mixtape

Bootstrapper

### Responsibilities

## Environment

## Mixtape_Model

### Extending Mixtape_Model: the Delegate

Controllers

### Mixtape loves

- FluentInterface https://martinfowler.com/bliki/FluentInterface.html
- Delegation, Composition, DI, SOLID.
- Testability (A side effect of dependency injection, objects can be swapped/stubbed/mocked)
- Generates prefixed classes of the library to avoid plugin conflicts (remixing)


Mixtape_Bootstrap
  Mixtape_Class_Loader
(loads classes and creates an Environment subclass instance from
 the specified lib location, with the specified prefix)


Environment
(contains rest bundle definitions and model definitions)

### Starting a new Project

Mixtape is meant to be used with a unique custom class prefix per project.
There is a script for creating a new project with a custom prefix. You can run it like so.

    /scripts/new_project.sh Custom_Prefix ./../plugin-name/lib/custom_prefix

Thie above will rename all mixtape classes: e.g. `Mixtape_Bootstrap -> Custom_Prefix_Bootstrap`

### Testing

Mixtape has a PHPUnit test suite. It can be setup like this (look into the script for possible options you can override such as db pass)

    ./tests/bin/install.sh
    ./vendor/bin/phpunit

You can also start a watcher script for fast TDD cycles

   ./tests/bin/watch.py

You will need Python 3 and pip (for installing dependencies).
