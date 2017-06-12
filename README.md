# Mixtape

Model, Data Store, Data Transfer Object and REST API Controller Library for WordPress

## Features

### Easy-to-use FluentInterface

Provides a DSL-Like way of defining Models, Controllers, Etc.(see https://martinfowler.com/bliki/FluentInterface.html)


#### Defining a Model's Fields
```php
<?php
function declare_fields( $d ) {
        return array(
            $d->field( 'id' )
                ->map_from( 'ID' )
                ->typed( $d->type( 'uint') )
                ->description( 'Unique identifier for the object.' ),

            $d->field( 'title', 'The casette title.' )
                ->map_from( 'post_title' )
                ->typed( $d->type( 'string') )
                ->required(),

            $d->field( 'author', __( 'The author identifier.', 'casette' ) )
                ->map_from( 'post_author' )
                ->typed( $d->type( 'uint') )
                ->validated_by( 'validate_author' )
                ->with_default( 0 )
                ->dto_name( 'authorID' ),

            $d->field( 'status', 'The casette status.' )
                ->typed( $d->type( 'string') )
                ->validated_by( 'validate_status' )
                ->with_default('draft')
                ->map_from( 'post_status' ),

            $d->field( 'ratings', 'The casette ratings' )
                ->derived( 'get_ratings' )
                ->dto_name( 'the_ratings' ),

            $d->field( 'songs', 'The casette songs', 'meta' )
                ->map_from( '_casette_song_ids' )
                ->typed( $d->type( 'array' ) )
                ->with_deserializer( 'song_before_return' )
                ->with_serializer( 'song_before_save' )
                ->dto_name( 'song_ids' ),
        );
}
```

#### Setting Up a new Plugin's Environment

```php
$env = $mixtape->environment();
$env->define()->model(
    'Casette'
)->with_data_store(
    $env->define()->data_store()
        ->custom_post_type()
        ->with_post_type('mixtape_casette')
);
$bundle = $env
    ->define()->rest_api('mixtape-example/v1');
$bundle->endpoint()
    ->crud( '/casettes' )
    ->for_model( $env->get()->model( 'Casette' ) );
$bundle->endpoint()
    ->with_class( 'CasetteApiEndpointVersion' );

$mixtape->environment()->start();
```

- Reusable (Delegation, Composition, DI, SOLID)
- Testable (A side effect of dependency injection, objects can be swapped/stubbed/mocked)
- Generates prefixed classes of the library to avoid plugin conflicts (remixing)

## Starting a new Project

Mixtape is meant to be used with a unique custom class prefix per project.
There is a script for creating a new project with a custom prefix. You can run it like so.

    /scripts/new_project.sh Custom_Prefix ./../plugin-name/lib/custom_prefix

Thie above will rename all mixtape classes: e.g. `Mixtape_Bootstrap -> Custom_Prefix_Bootstrap`

## Testing

Mixtape has a PHPUnit test suite. It can be setup like this (look into the script for possible options you can override such as db pass)

    ./tests/bin/install.sh
    ./vendor/bin/phpunit

You can also start a watcher script for fast TDD cycles

   ./tests/bin/watch.py

You will need Python 3 and pip (for installing dependencies).
