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
- Delegation, Composition
- Interfaces
- Can be used peacemeal
- Tests (A side effect of dependency injection, objects can be swapped/stubbed/mocked)
- WP Hooks the WP hook way

Hates

- Conflicts (? Optional Wrapper that creates a uniquely prefixed class hierarchy per plugin)

Mixtape_Main
  Mixtape_Class_Loader
(loads classes and creates an Environment subclass instance from
 the specified lib location, with the specified prefix)


Environment
(holds globals such as data_stores, rest bundles, model factories etc)

    (a registry instance. One per environment)
    Map<String, Mixtape_Interfaces_DataStore> data_stores
    Map<String, Mixtape_Rest_Api_Bundle> rest_api_bundles
    Array<String, Mixtape_Interfaces_Model>
