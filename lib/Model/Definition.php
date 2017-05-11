<?php

class Mixtape_Model_Definition {

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
     * @var Mixtape_Model_Declaration
     */
    private $delegate;
    /**
     * @var strings
     */
    private $name;
    /**
     * @var Mixtape_Model_Field_Sanitizer
     */
    private $sanitizer;

    function __construct( $environment, $delegate, $data_store ) {
        $this->field_declarations = null;
        $this->environment = $environment;
        $this->delegate = $delegate;
        $this->model_class = get_class( $delegate );
        $this->sanitizer = new Mixtape_Model_Field_Sanitizer();
        $this->set_data_store( $data_store );
    }

    function get_model_class() {
        return $this->model_class;
    }

    function get_data_store() {
        return $this->data_store;
    }

    function set_data_store( $data_store ) {
        $this->data_store = $data_store;
        $this->data_store->set_definition( $this );
        return $this;
    }

    function get_environment() {
        return $this->environment;
    }

    public function get_field_declarations( $filter_by_type=null ) {
        $delegate = $this->get_delegate();
        $interface = $this->get_environment()->get_bootstrap()->class_loader()->prefixed_class_name( 'Interfaces_Model_Declaration' );

        if ( !is_a( $delegate, $interface ) ) {
            throw new Mixtape_Exception( $this->get_model_class() . ' is not a subclass of ' . $interface );
        }

        if ( null ===$this->field_declarations ) {
            $fields = $delegate->declare_fields( $this );

            $this->field_declarations = $this->initialize_field_map( $fields );
        }
        if ( null === $filter_by_type ) {
            return $this->field_declarations;
        }
        $filtered = array();
        foreach ($this->field_declarations as $field_declaration ) {
            if ( $field_declaration->type === $filter_by_type ) {
                $filtered[] = $field_declaration;
            }
        }
        return $filtered;
    }

    public function create_instance( $entity ) {
        if (is_array( $entity ) ) {
            return new Mixtape_Model( $this, $entity );
        }
        throw new Mixtape_Exception('does not understand entity');
    }

    /**
     * @param Mixtape_Model $model
     * @param WP_REST_Request $request
     * @param bool $updating
     * @return Mixtape_Model
     * @throws Mixtape_Exception
     */
    public function merge_updates_from_request( $model, $request, $updating = false ) {
        $request_data = $this->map_request_data( $request, $updating );
        foreach ( $request_data as $name => $value ) {
            $model->set( $name, $value );
        }
        return $model;
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
                $custom_sanitization = $field->get_sanitize();
                if ( ! empty( $custom_sanitization ) ) {
                    $value = $this->get_delegate()->call( $custom_sanitization, array( $value ) );
                } else {
                    $value = $this->sanitizer->sanitize( $field, $value );
                }
                $request_data[$field_name] = $value;
            }
        }
        return $request_data;
    }

    function field( $name = null, $description = null ) {
        $builder = new Mixtape_Model_Field_DeclarationBuilder();
        if ( ! empty( $name ) ) {
            $builder->named( $name );
        }
        if ( ! empty( $description ) ) {
            $builder->with_description( $description );
        }
        return $builder;
    }

    function meta_field( $name = null, $description = null ) {
        return $this->field( $name, $description )->with_field_type( Mixtape_Model_Field_Types::META );
    }

    function derived_field( $name = null, $description = null ) {
        return $this->field( $name, $description )->with_field_type( Mixtape_Model_Field_Types::DERIVED );
    }

    public function get_delegate() {
        return $this->delegate;
    }

    /**
     * @param WP_REST_Request $request
     * @return Mixtape_Model
     */
    public function new_from_request( $request ) {
        $field_data = $this->map_request_data( $request, false );
        return $this->create_instance( $field_data );
    }

    public function get_dto_field_mappings() {
        $mappings = array();
        foreach ( $this->get_field_declarations() as $field_declaration ) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            if ( !$field_declaration->suppports_output_type( 'json' ) ) {
                continue;
            }
            $mappings[$field_declaration->get_data_transfer_name()] = $field_declaration->get_name();
        }
        return $mappings;
    }

    public function all() {
        return $this->get_data_store()->get_entities();
    }

    public function find_one_by_id( $id) {
        $entity = $this->get_data_store()->get_entity( $id );
        return !empty( $entity ) ? $entity : null;
    }

    /**
     * @param $declared_field_builders array of Mixtape_Model_Field_Declaration_Builder
     * @return array
     */
    private function initialize_field_map( $declared_field_builders ) {
        $fields = array(
        );
        foreach ( $declared_field_builders as $field_builder ) {
            $field = $field_builder->build();
            $fields[$field->name] = $field;
        }
        return $fields;
    }

    public function get_name() {
        return $this->name;
    }

    /**
     * @param WP_REST_Request $request
     * @return bool
     */
    public function permissions_check( $request, $action ) {
        return true;
    }
}