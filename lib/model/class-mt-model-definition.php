<?php
/**
 * The model definition
 *
 * @pacage Mixtape/Model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Model_Definition
 */
class MT_Model_Definition implements MT_Interfaces_Permissions_Provider {

	/**
	 * Environment
	 *
	 * @var MT_Environment
	 */
	private $environment;
	/**
	 * Field Declarations
	 *
	 * @var array
	 */
	private $field_declarations;
	/**
	 * Model class
	 *
	 * @var string
	 */
	private $model_class;
	/**
	 * Data Store
	 *
	 * @var MT_Interfaces_Data_Store
	 */
	private $data_store;

	/**
	 * Model Declaration
	 *
	 * @var MT_Interfaces_Model_Declaration
	 */
	private $model_declaration;

	/**
	 * Name
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Permissions Provider
	 *
	 * @var MT_Interfaces_Permissions_Provider
	 */
	private $permissions_provider;

	/**
	 * Mixtape_Model_Definition constructor.
	 *
	 * @param MT_Environment                                      $environment The Environment.
	 * @param MT_Interfaces_Model_Declaration                     $model_declaration Declaration.
	 * @param MT_Interfaces_Data_Store|MT_Data_Store_Builder $data_store Store.
	 * @param MT_Interfaces_Permissions_Provider         $permissions_provider Provider.
	 *
	 * @throws MT_Exception Throws if wrong types or null args provided.
	 */
	function __construct( $environment, $model_declaration, $data_store, $permissions_provider ) {
		MT_Expect::that( null !== $environment         , '$environment cannot be null' );
		MT_Expect::that( null !== $model_declaration   , '$model_declaration cannot be null' );
		MT_Expect::that( null !== $data_store          , '$data_store cannot be null' );
		MT_Expect::that( null !== $permissions_provider, '$permissions_provider cannot be null' );
		// Fail if provided with inappropriate types.
		MT_Expect::is_a( $environment         , 'MT_Environment');
		MT_Expect::is_a( $model_declaration   , 'MT_Interfaces_Model_Declaration');
		MT_Expect::is_a( $permissions_provider, 'MT_Interfaces_Permissions_Provider');

		$this->field_declarations   = null;
		$this->environment          = $environment;
		$this->model_declaration    = $model_declaration;
		$this->model_class          = get_class( $model_declaration );
		$this->permissions_provider = $permissions_provider;
		$this->name                 = strtolower( $this->model_class );

		$this->set_data_store( $data_store );
	}

	/**
	 * Get Model Class
	 *
	 * @return string
	 */
	function get_model_class() {
		return $this->model_class;
	}

	/**
	 * Get Data Store
	 *
	 * @return MT_Interfaces_Data_Store
	 */
	function get_data_store() {
		return $this->data_store;
	}

	/**
	 * Set the Data Store
	 *
	 * @param MT_Interfaces_Data_Store|MT_Data_Store_Builder $data_store A builder or a Data store.
	 * @return $this
	 * @throws MT_Exception Throws when Data Store Invalid.
	 */
	function set_data_store( $data_store ) {
		if ( is_a( $data_store, 'MT_Data_Store_Builder') ) {
			$this->data_store = $data_store
				->with_model_definition( $this )
				->build();
		} else {
			$this->data_store = $data_store;
		}
		// at this point we should have a data store.
		MT_Expect::is_a( $this->data_store, 'MT_Interfaces_Data_Store');

		return $this;
	}

	/**
	 * Environment
	 *
	 * @return MT_Environment
	 */
	function environment() {
		return $this->environment;
	}

	/**
	 * Get this Definition's Field Declarations
	 *
	 * @param null|string $filter_by_type The type to filter with.
	 *
	 * @return array|null
	 */
	function get_field_declarations( $filter_by_type = null ) {
		$model_declaration = $this->get_model_declaration()->set_definition( $this );

		MT_Expect::is_a( $model_declaration, 'MT_Interfaces_Model_Declaration');

		if ( null === $this->field_declarations ) {
			$builder = new MT_Model_Field_Declaration_Collection_Builder( $this->environment() );
			$fields = $model_declaration->declare_fields( $builder );

			$this->field_declarations = $this->initialize_field_map( $fields );
		}
		if ( null === $filter_by_type ) {
			return $this->field_declarations;
		}
		$filtered = array();
		foreach ( $this->field_declarations as $field_declaration ) {
			if ( $field_declaration->get_type() === $filter_by_type ) {
				$filtered[] = $field_declaration;
			}
		}
		return $filtered;
	}

	/**
	 * Create a new Model Instance
	 *
	 * @param array $data The data.
	 *
	 * @return MT_Model
	 * @throws MT_Exception Throws if data not an array.
	 */
	function create_instance( $data ) {
		if ( is_array( $data ) ) {
			return new MT_Model( $this, $data );
		}
		throw new MT_Exception( 'does not understand entity' );
	}

	/**
	 * Merge values from HTTP Request with current values.
	 * Note: Values change in place.
	 *
	 * @param MT_Interfaces_Model $model The model.
	 * @param WP_REST_Request          $request The request.
	 * @param bool                     $updating Is this an update?.
	 * @return MT_Model
	 * @throws MT_Exception Throws.
	 */
	function merge_updates_from_request( $model, $request, $updating = false ) {
		$request_data = $this->map_request_data( $request, $updating );
		foreach ( $request_data as $name => $value ) {
			$model->set( $name, $value );
		}
		return $model->sanitize();
	}

	/**
	 * Get Model Declaration
	 *
	 * @return MT_Interfaces_Model_Declaration
	 */
	public function get_model_declaration() {
		return $this->model_declaration;
	}

	/**
	 * Creates a new Model From a Request
	 *
	 * @param WP_REST_Request $request The request.
	 * @return MT_Model
	 */
	public function new_from_request( $request ) {
		$field_data = $this->map_request_data( $request, false );
		return $this->create_instance( $field_data )->sanitize();
	}

	/**
	 * Get field DTO Mappings
	 *
	 * @return array
	 */
	function get_dto_field_mappings() {
		$mappings = array();
		foreach ( $this->get_field_declarations() as $field_declaration ) {
			/** @var MT_Model_Field_Declaration $field_declaration */
			if ( ! $field_declaration->supports_output_type( 'json' ) ) {
				continue;
			}
			$mappings[ $field_declaration->get_data_transfer_name() ] = $field_declaration->get_name();
		}
		return $mappings;
	}

	/**
	 * Prepare the Model for Data Transfer
	 *
	 * @param MT_Interfaces_Model $model The model.
	 *
	 * @return array
	 */
	function model_to_dto( $model ) {
		$result = array();
		foreach ( $this->get_dto_field_mappings() as $mapping_name => $field_name ) {
			$value = $model->get( $field_name );
			$result[ $mapping_name ] = $value;
		}

		return $result;
	}

	/**
	 * Get Name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Check permissions
	 *
	 * @param WP_REST_Request $request The request.
	 * @param string          $action The action.
	 * @return bool
	 */
	public function permissions_check( $request, $action ) {
		return $this->permissions_provider->permissions_check( $request, $action );
	}

	/**
	 * Map Request data
	 *
	 * @param WP_REST_Request $request the Request.
	 * @param bool            $updating Is update.
	 *
	 * @return array
	 */
	private function map_request_data( $request, $updating = false ) {
		$request_data = array();
		$fields = $this->get_field_declarations();
		foreach ( $fields as $field ) {
			/**
			 * Field
			 *
			 * @var MT_Model_Field_Declaration $field Field.
			 */
			if ( $field->is_derived_field() ) {
				continue;
			}
			$dto_name = $field->get_data_transfer_name();
			$field_name = $field->get_name();
			if ( isset( $request[ $dto_name ] ) && ! ( $updating && $field->is_primary() ) ) {
				$value = $request[ $dto_name ];
				$request_data[ $field_name ] = $value;
			}
		}
		return $request_data;
	}

	/**
	 * Initialize_field_map
	 *
	 * @param array $declared_field_builders Array<Mixtape_Model_Field_Declaration_Builder>.
	 *
	 * @return array
	 */
	private function initialize_field_map( $declared_field_builders ) {
		$fields = array();
		foreach ( $declared_field_builders as $field_builder ) {
			/**
			 * Builder
			 *
			 * @var MT_Model_Field_Declaration $field Field Builder.
			 */
			$field = $field_builder->build();
			$fields[ $field->get_name() ] = $field;
		}
		return $fields;
	}
}
