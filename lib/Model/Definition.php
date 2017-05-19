<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Model_Definition implements Mixtape_Interfaces_Rest_Api_Permissions_Provider {

    /**
     * @var Mixtape_Environment
     */
    private $environment;
    /**
     * @var array
     */
    private $field_declarations;
    /**
     * @var string
     */
    private $model_class;
    /**
     * @var Mixtape_Interfaces_Data_Store
     */
    private $data_store;
    /**
     * @var Mixtape_Interfaces_Model_Declaration
     */
    private $model_declaration;
    /**
     * @var string
     */
    private $name;
    /**
     * @var Mixtape_Interfaces_Rest_Api_Permissions_Provider
     */
    private $permissions_provider;

    /**
     * Mixtape_Model_Definition constructor.
     * @param Mixtape_Environment $environment
     * @param Mixtape_Interfaces_Model_Declaration $model_declaration
     * @param Mixtape_Interfaces_Data_Store|Mixtape_Data_Store_Builder $data_store
     * @param Mixtape_Interfaces_Rest_Api_Permissions_Provider $permissions_provider
     * @throws Mixtape_Exception
     */
    function __construct( $environment, $model_declaration, $data_store, $permissions_provider ) {
        Mixtape_Expect::that( $environment !== null         , '$environment cannot be null' );
        Mixtape_Expect::that( $model_declaration !== null   , '$model_declaration cannot be null' );
        Mixtape_Expect::that( $data_store !== null          , '$data_store cannot be null' );
        Mixtape_Expect::that( $permissions_provider !== null, '$permissions_provider cannot be null' );
        // fail if provided with inappropriate types
        Mixtape_Expect::is_a( $environment, 'Mixtape_Environment' );
        Mixtape_Expect::is_a( $model_declaration, 'Mixtape_Interfaces_Model_Declaration' );
        Mixtape_Expect::is_a( $permissions_provider, 'Mixtape_Interfaces_Rest_Api_Permissions_Provider' );

        $this->field_declarations   = null;
        $this->environment          = $environment;
        $this->model_declaration    = $model_declaration;
        $this->model_class          = get_class( $model_declaration );
        $this->permissions_provider = $permissions_provider;
        $this->name                 = strtolower( $this->model_class );
        $this->set_data_store( $data_store );
    }

    function get_model_class() {
        return $this->model_class;
    }

    function get_data_store() {
        return $this->data_store;
    }

    /**
     * @param Mixtape_Interfaces_Data_Store|Mixtape_Data_Store_Builder $data_store
     * @return $this
     */
    function set_data_store( $data_store ) {
        if ( is_a( $data_store, 'Mixtape_Data_Store_Builder' ) ) {
            $this->data_store = $data_store
                ->with_model_definition( $this )
                ->build();
        } else {
            $this->data_store = $data_store;
        }
        // at this point we should have a data store
        Mixtape_Expect::is_a( $this->data_store, 'Mixtape_Interfaces_Data_Store' );

        return $this;
    }

    function environment() {
        return $this->environment;
    }

    function get_field_declarations( $filter_by_type=null ) {
        $model_declaration = $this->get_model_declaration()->set_definition( $this );

        Mixtape_Expect::is_a( $model_declaration, 'Mixtape_Interfaces_Model_Declaration' );

        if ( null === $this->field_declarations ) {
            $builder = new Mixtape_Model_Field_Declaration_Collection_Builder( $this->environment() );
            $fields = $model_declaration->declare_fields( $builder );

            $this->field_declarations = $this->initialize_field_map( $fields );
        }
        if ( null === $filter_by_type ) {
            return $this->field_declarations;
        }
        $filtered = array();
        foreach ($this->field_declarations as $field_declaration ) {
            if ( $field_declaration->get_type() === $filter_by_type ) {
                $filtered[] = $field_declaration;
            }
        }
        return $filtered;
    }

    function create_instance( $entity ) {
        if (is_array( $entity ) ) {
            return new Mixtape_Model( $this, $entity );
        }
        throw new Mixtape_Exception( 'does not understand entity' );
    }

    /**
     * @param Mixtape_Model $model
     * @param WP_REST_Request $request
     * @param bool $updating
     * @return Mixtape_Model
     * @throws Mixtape_Exception
     */
    function merge_updates_from_request( $model, $request, $updating = false ) {
        $request_data = $this->map_request_data( $request, $updating );
        foreach ( $request_data as $name => $value ) {
            $model->set( $name, $value );
        }
        return $model->sanitize();
    }

    public function get_model_declaration() {
        return $this->model_declaration;
    }

    /**
     * @param WP_REST_Request $request
     * @return Mixtape_Model
     */
    public function new_from_request( $request ) {
        $field_data = $this->map_request_data( $request, false );
        return $this->create_instance( $field_data )->sanitize();
    }

    function get_dto_field_mappings() {
        $mappings = array();
        foreach ( $this->get_field_declarations() as $field_declaration ) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            if ( !$field_declaration->supports_output_type( 'json' ) ) {
                continue;
            }
            $mappings[$field_declaration->get_data_transfer_name()] = $field_declaration->get_name();
        }
        return $mappings;
    }

    function model_to_dto( $model ) {
        $result = array();
        foreach ($this->get_dto_field_mappings() as $mapping_name => $field_name ) {
            $value = $model->get( $field_name );
            $result[$mapping_name] = $value;
        }

        return $result;
    }

    public function find_one_by_id( $id) {
        $entity = $this->get_data_store()->get_entity( $id );
        return !empty( $entity ) ? $entity : null;
    }

    public function get_name() {
        return $this->name;
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function permissions_check( $request, $action ) {
        return $this->permissions_provider->permissions_check( $request, $action );
    }

    private function map_request_data( $request, $updating = false ) {
        $request_data = array();
        $fields = $this->get_field_declarations();
        foreach ( $fields as $field ) {
            /** @var Mixtape_Model_Field_Declaration $field */
            if ( $field->is_derived_field() ) {
                continue;
            }
            $dto_name = $field->get_data_transfer_name();
            $field_name = $field->get_name();
            if ( isset( $request[$dto_name] ) && !( $updating && $field->is_primary() ) ) {
                $value = $request[$dto_name];
                $request_data[$field_name] = $value;
            }
        }
        return $request_data;
    }

    /**
     * @param $declared_field_builders array of Mixtape_Model_Field_Declaration_Builder
     * @return array
     */
    private function initialize_field_map( $declared_field_builders ) {
        $fields = array(
        );
        foreach ( $declared_field_builders as $field_builder ) {
            /** @var Mixtape_Model_Field_Declaration $field */
            $field = $field_builder->build();
            $fields[$field->get_name()] = $field;
        }
        return $fields;
    }
}