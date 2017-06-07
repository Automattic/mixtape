<?php
/**
 * A Controller that is related to a Model Declaration.
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Model
 * Knows about models
 */
class MT_Controller_Model extends MT_Controller {
	/**
	 * The Definition
	 *
	 * @var MT_Model_Definition
	 */
	protected $model_definition;
	/**
	 * The Declaration
	 *
	 * @var MT_Model_Declaration
	 */
	protected $model_declaration;
	/**
	 * The data Store
	 *
	 * @var MT_Interfaces_Data_Store
	 */
	protected $model_data_store;

	/**
	 * Mixtape_Rest_Api_Controller_CRUD constructor.
	 *
	 * @param MT_Controller_Bundle $controller_bundle A Bundle.
	 * @param string               $base The baser.
	 * @param MT_Model_Definition  $model_definition A Definition.
	 */
	public function __construct( $controller_bundle, $base, $model_definition ) {
		$this->base = $base;
		parent::__construct( $controller_bundle, $model_definition->environment() );
		$this->model_definition = $model_definition;
		$this->model_declaration = $this->model_definition->get_model_declaration();
		$this->model_data_store = $this->model_definition->get_data_store();
	}

	/**
	 * Get our model definition
	 *
	 * @return MT_Model_Definition
	 */
	protected function get_model_definition() {
		return $this->model_definition;
	}

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * In our case, it gets fields/types from our definition's declared fields.
	 *
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$model_definition = $this->get_model_definition();
		$fields = $model_definition->get_field_declarations();
		$properties = array();
		$required = array();
		foreach ( $fields as $field_declaration ) {
			/**
			 * Our declaration
			 *
			 * @var MT_Model_Field_Declaration $field_declaration
			 */
			$properties[ $field_declaration->get_data_transfer_name() ] = $field_declaration->as_item_schema_property();
			if ( $field_declaration->is_required() ) {
				$required[] = $field_declaration->get_data_transfer_name();
			}
		}
		$schema = array(
			'$schema' => 'http://json-schema.org/schema#',
			'title' => $model_definition->get_name(),
			'type' => 'object',
			'properties' => (array) apply_filters( 'mixtape_rest_api_schema_properties', $properties, $this->get_model_definition() ),
		);

		if ( ! empty( $required ) ) {
			$schema['required'] = $required;
		}

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get Model Declaration
	 *
	 * @return MT_Model_Declaration
	 */
	protected function get_model_declaration() {
		return $this->model_declaration;
	}

	/**
	 * Get Model DataStore
	 *
	 * @return MT_Interfaces_Data_Store
	 */
	protected function get_model_data_store() {
		return $this->model_data_store;
	}

	/**
	 * Permissions for get_items
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return $this->permissions_check( $request, 'index' );
	}

	/**
	 * Permissions for get_item
	 *
	 * @param WP_REST_Request $request The request.
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->permissions_check( $request, 'show' );
	}

	/**
	 * Permissions for create_item
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public function create_item_permissions_check( $request ) {
		return $this->permissions_check( $request, 'create' );
	}

	/**
	 * Permissions for update_item
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		return $this->permissions_check( $request, 'update' );
	}

	/**
	 * Permissions for delete_item
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool
	 */
	public function delete_item_permissions_check( $request ) {
		return $this->permissions_check( $request, 'delete' );
	}

	/**
	 * Generic Permissions Check.
	 *
	 * @param WP_REST_Request $request Request.
	 * @param string          $action One of (index, show, create, update, delete).
	 * @return bool
	 */
	private function permissions_check( $request, $action ) {
		return $this->get_model_definition()->permissions_check( $request, $action );
	}

	/**
	 * Prepare Entity to be a DTO
	 *
	 * @param array|MT_Model_Collection|MT_Interfaces_Model $entity The Entity.
	 * @return array
	 */
	protected function prepare_dto( $entity ) {
		if ( is_array( $entity ) ) {
			return $entity;
		}

		if ( is_a( $entity, 'MT_Model_Collection' ) ) {
			$results = array();
			foreach ( $entity->get_items() as $model ) {
				$results[] = $this->model_to_dto( $model );
			}
			return $results;
		}

		if ( is_a( $entity, 'MT_Interfaces_Model' ) ) {
			return $this->model_to_dto( $entity );
		}

		return $entity;
	}

	/**
	 * Map a model to a Data Transfer Object (plain array)
	 *
	 * @param MT_Interfaces_Model $model The Model.
	 * @return array
	 */
	protected function model_to_dto( $model ) {
		return $this->get_model_definition()->model_to_dto( $model );
	}

	/**
	 * Prepare the item for create or update operation.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		return $this->get_model_definition()->new_from_request( $request );
	}

	/**
	 * Get base url
	 *
	 * @return string
	 */
	protected function get_base_url() {
		return rest_url( $this->controller_bundle->get_prefix() . $this->base );
	}
}
