# Mixtape

Model, Data Store, Data Transfer Object and REST API Controller Library for WordPress

[![Build Status](https://travis-ci.org/Automattic/mixtape.svg?branch=master)](https://travis-ci.org/Automattic/mixtape)

## Features

### Easy-to-use Fluent Interface

Provides a DSL-Like way of defining Models, Controllers, Etc.(see https://martinfowler.com/bliki/FluentInterface.html)


#### Defining a Model's Fields
```php
<?php
public function declare_fields( $d ) {
			return array(
				$d->field( 'id' )
					->with_map_from( 'ID' )
					->with_type( $d->type( 'uint' ) )
					->with_description( 'Unique identifier for the object.' ),

				$d->field( 'title', 'The casette title.' )
					->with_map_from( 'post_title' )
					->with_type( $d->type( 'string' ) )
					->with_required(),

				$d->field( 'author', __( 'The author identifier.', 'casette' ) )
					->with_map_from( 'post_author' )
					->with_type( $d->type( 'uint' ) )
					->with_validations( 'validate_author' )
					->with_default( 0 )
					->with_dto_name( 'authorID' ),

				$d->field( 'status', 'The casette status.' )
					->with_type( $d->type( 'string' ) )
					->with_validations( 'validate_status' )
					->with_default( 'draft' )
					->with_map_from( 'post_status' ),

				$d->field( 'ratings', 'The casette ratings' )
					->derived( array( $this, 'get_ratings' ) )
					->with_dto_name( 'the_ratings' ),

				$d->field( 'songs', 'The casette songs', 'meta' )
					->with_map_from( '_casette_song_ids' )
					->with_type( $d->type( 'array' ) )
					->with_deserializer( array( $this, 'song_before_return' ) )
					->with_serializer( array( $this, 'song_before_save' ) )
					->with_dto_name( 'song_ids' ),
			);
	}
```

#### Setting Up a new Plugin's Environment

```php
static function register( $bootstrap ) {
		$env = $bootstrap->environment();
		$cpt_data_store = $env->data_store()
			->with_class( 'MT_Data_Store_CustomPostType' )
			->with_args( array(
				'post_type' => 'mixtape_cassette',
			) );

		$env->define_model( 'Casette' )
			->with_data_store( $cpt_data_store );

		$env->define_model( 'CasetteSettings' )
			->with_data_store( $env->data_store()->with_class( 'MT_Data_Store_Option' ) );

		$rest_api = $env->rest_api( 'mixtape-example/v1' );

		$rest_api->endpoint()
			->with_base( '/casettes' )
			->with_class( 'MT_Controller_CRUD' )
			->for_model( $env->model( 'Casette' ) );

		$rest_api->endpoint()
			->with_class( 'CasetteApiEndpointVersion' );

		$rest_api->endpoint()
			->with_base( '/settings' )
			->with_class( 'MT_Controller_Settings' )
			->for_model( $env->model( 'CasetteSettings' ) );

		$env->auto_start();
	}
```

- Reusable (Delegation, Composition, DI, SOLID)
- Testable (A side effect of dependency injection, objects can be swapped/stubbed/mocked)
- Generates prefixed classes of the library to avoid plugin conflicts (remixing)

## Starting a new Project

Mixtape is meant to be used with a unique custom class prefix per project.
There is a script for creating a new project with a custom prefix. You can run it like so.

    /scripts/new_project.sh Custom_Prefix ./../plugin-name/lib/custom_prefix

The above will rename all mixtape classes: e.g. `Mixtape_Bootstrap -> Custom_Prefix_Bootstrap`

## Testing

Mixtape has a PHPUnit test suite. It can be setup like this (look into the script for possible options you can override such as db pass)

    ./tests/bin/install.sh
    ./vendor/bin/phpunit

You can also start a watcher script for fast TDD cycles

   ./tests/bin/watch.py

You will need Python 3 and pip (for installing dependencies).
