<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CasetteAdminSettings {
	static function get_settings() {
		return array(
			'casette_general' => array(
				__( 'Casette General', 'mixtape' ),
				array(
					array(
						'name'        => 'mixtape_casette_per_page',
						'std'         => '10',
						'placeholder' => '',
						'label'       => __( 'Casettes Per Page', 'mixtape' ),
						'desc'        => __( 'How many listings should be shown per page by default?', 'mixtape' ),
						'attributes'  => array(),
					),
					array(
						'name'       => 'mixtape_casette_hide_listened',
						'std'        => '0',
						'label'      => __( 'Hide Listened Casettes', 'mixtape' ),
						'cb_label'   => __( 'Hide Listened Casettes', 'mixtape' ),
						'desc'       => __( 'If enabled, listened Casettes will be hidden from archives.', 'mixtape' ),
						'type'       => 'checkbox',
						'attributes' => array(),
					),
					array(
						'name'       => 'mixtape_casette_enable_private',
						'std'        => '0',
						'label'      => __( 'Users can create private Casettes', 'mixtape' ),
						'cb_label'   => __( 'Users can create private Casettes', 'mixtape' ),
						'desc'       => __( 'If enabled, Users can create private Casettes (defaults to false)', 'mixtape' ),
						'type'       => 'checkbox',
						'attributes' => array(),
					),
				),
			),
		);
	}
}

class CasetteSettings extends MT_Model_Settings {
	static function on_field_setup( $field_name, $field_builder, $field_data, $env ) {
		$field_builder->with_dto_name( str_replace( 'mixtape_casette_', '', $field_data['name'] ) );
	}

	public static function get_settings() {
		return CasetteAdminSettings::get_settings();
	}
}

class Casette extends MT_Model {
	public static function declare_fields() {
		$d = self::get_environment();
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
					->derived( 'get_ratings' )
					->with_dto_name( 'the_ratings' ),

				$d->field( 'songs', 'The casette songs', 'meta' )
					->with_map_from( '_casette_song_ids' )
					->with_type( $d->type( 'array' ) )
					->with_deserializer( 'song_before_return' )
					->with_serializer( 'song_before_save' )
					->with_dto_name( 'song_ids' ),
			);
	}

	public function get_ratings() {
		return array( 1 );
	}

	public function get_id() {
		return $this->get( 'id' );
	}

	public function get_name() {
		return 'mixtape_casette';
	}

	protected function validate_author( $author_id ) {
		$author = $this->get_author( $author_id );
		if ( null === $author ) {
			return new WP_Error( 'invalid-author-id', __( 'Invalid author id', 'casette' ) );
		}
		return true;
	}

	protected function validate_status( $status ) {
		if ( 'publish' === $status ) {
			$author_id = $this->get( 'author' );
			if ( empty( $author_id ) || null === $this->get_author( $author_id ) ) {
				return new WP_Error( 'missing-author-id', __( 'Cannot publish when author is empty', 'casette' ) );
			}
		}

		return true;
	}

	private function get_author( $author_id ) {
		return get_user_by( 'id', $author_id );
	}

	function song_before_return( $value ) {
		return array_map( 'absint', explode( ',', $value ) );
	}

	function song_before_save( $value ) {
		return implode( ',', $value );
	}

	/**
	 * @param WP_REST_Request $request
	 * @return bool
	 */
	public static function permissions_check( $request, $action ) {
		return true;
	}

	/**
	 * Models should override this. Provide the class name as a string. Ensures our static calls work for php < 5.3
	 *
	 * @return string
	 */
	public static function get_class() {
		return __CLASS__;
	}
}

class CasetteApiEndpointVersion extends MT_Controller {
	/**
	 * @var string the endpoint base
	 */
	protected $base = '/version';

	/**
	 * Setup
	 */
	public function setup() {
		$this->add_route()
			->handler( WP_REST_Server::READABLE, array( $this, 'get_items' ) );
	}

	/**
	 * Get Items
	 *
	 * @param WP_REST_Request $request Req.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		return new WP_REST_Response( array(
			'mixtape-example-version' => '0.1.0',
		), 200 );
	}

	/**
	 * Permissions,
	 *
	 * @param WP_REST_Request $request R.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}
}

/**
 * Class CasetteRESTApi
 */
class CasetteRESTApi {
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

		$env->define_model( 'CasetteSettings' )
			->with_data_store( new MT_Data_Store_Option( $env->model( 'CasetteSettings' ) ) );


		$rest_api = $env->rest_api( 'mixtape-example/v1' );

		$rest_api->add_endpoint( new MT_Controller_CRUD( '/casettes', 'Casette' ) );
		$rest_api->add_endpoint( new CasetteApiEndpointVersion() );
		$rest_api->add_endpoint( new MT_Controller_Settings( '/settings', 'CasetteSettings' ) );

		$env->auto_start();
	}
}
