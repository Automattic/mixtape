<?php
/**
 * Controller for handling settings
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Settings
 */
class MT_Controller_Settings extends MT_Controller_Model {

	/**
	 * Register this.
	 */
	function register() {
		$prefix = $this->controller_bundle->get_prefix();

		register_rest_route( $prefix, $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
		) );
	}

	/**
	 * Get Settings
	 *
	 * @param WP_REST_Request $request The request.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$model = $this->model_definition->get_data_store()->get_entity( null );
		if ( empty( $model ) ) {
			return $this->not_found( __( 'Settings not found' ) );
		}

		return $this->succeed( $this->prepare_dto( $model ) );
	}

	/**
	 * Create or Update settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function create_item( $request ) {
		return $this->create_or_update( $request );
	}

	/**
	 * Create or Update a Model
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	protected function create_or_update( $request ) {
		$is_update = $request->get_method() !== 'POST';
		$model_to_update = $this->model_definition->get_data_store()->get_entity( null );
		if ( empty( $model_to_update ) ) {
			return $this->not_found( 'Model does not exist' );
		}

		$model = $this->get_model_definition()->merge_updates_from_request( $model_to_update, $request, true );

		if ( is_wp_error( $model ) ) {
			return $this->bad_request( $model );
		}

		$validation = $model->validate();
		if ( is_wp_error( $validation ) ) {
			return $this->bad_request( $validation );
		}

		$id_or_error = $this->model_data_store->upsert( $model );

		if ( is_wp_error( $id_or_error ) ) {
			return $this->bad_request( $id_or_error );
		}

		$dto = $this->prepare_dto( array(
			'id' => absint( $id_or_error ),
		) );

		return $is_update ? $this->succeed( $dto ) : $this->created( $dto );
	}
}
