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
     * @var Mixtape_Model_Delegate
     */
    private $delegate;

    function __construct( $environment, $delegate, $data_store ) {
        $this->field_declarations = null;
        $this->environment = $environment;
        $this->delegate = $delegate;
        $this->model_class = get_class( $delegate );
        $this->data_store = $data_store;
        $this->data_store->set_definition( $this );
    }

    function get_model_class() {
        return $this->model_class;
    }

    function get_data_store() {
        return $this->data_store;
    }

    function get_environment() {
        return $this->environment;
    }

    public function get_field_declarations( $filter_by_type=null ) {
        $delegate = $this->get_delegate();
        $interface = $this->get_environment()->get_main()->class_loader()->prefixed_class_name( 'Interfaces_Model_Delegate' );

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
        if ( is_numeric( $entity  ) ) {
            return $this->get_data_store()->get_entity( $entity );
        }
        throw new Mixtape_Exception('does not understand entity');
    }

    /**
     * @param Mixtape_Model $model
     * @param WP_REST_Request $request
     * @param bool $updating
     * @return Mixtape_Model
     * @throws Mixtape_Exception
     * @internal param WP_REST_Request $other
     */
    public function merge_updates_from_request( $model, $request, $updating = false ) {
        $fields = $this->get_field_declarations();
        foreach ( $fields as $field ) {
            /** @var Mixtape_Model_Field_Declaration $field */
            if ( $field->is_derived_field() ) {
                continue;
            }
            if ( isset( $request[$field->get_name()] ) && !( $updating && $field->is_primary() ) ) {
                $model->set( $field->get_name(), $request[$field->get_name()] );
            }
        }
        return $model;
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
     * @internal Mixtape_Model_Field_Declaration $field
     */
    public function new_from_request( $request ) {
        $fields = $this->get_field_declarations();
        $field_data = array();
        foreach ($fields as $field) {
            if (isset($request[$field->name])) {
                $field_data[$field->name] = $request[$field->name];
            } else {
                $field_data[$field->name] = $field->get_default_value();
            }
        }

        return $this->create_instance( $field_data );
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
}