<?php

/**
 * Class Casette_Api
 */
class Casette_Api {
	/**
	 * Register our rest api
	 *
	 * @param MT_Bootstrap $bootstrap Mixtape.
	 */
	static function register( $bootstrap ) {
		$env = $bootstrap->environment();

		$env->define_model( 'Casette' )->with_data_store( new MT_Data_Store_CustomPostType( $env->model( 'Cassette' ), array(
			'post_type' => 'mixtape_cassette',
		) ) );

		$env->define_model( 'Casette_Settings' )
			->with_data_store( new MT_Data_Store_Option( $env->model( 'Casette_Settings' ) ) );


		$rest_api = $env->rest_api( 'mixtape-example/v1' );

		$rest_api->add_endpoint( new MT_Controller_CRUD( '/casettes', 'Casette' ) );
		$rest_api->add_endpoint( new Casette_Api_Endpoint_Version() );
		$rest_api->add_endpoint( new MT_Controller_Settings( '/settings', 'Casette_Settings' ) );

		$env->auto_start();
	}
}