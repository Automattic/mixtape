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
        $interface = $this->environment->get_main()->class_loader()->prefixed_class_name( 'Interfaces_Model_Delegate' );

        if ( !class_implements( $delegate, $interface ) ) {
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

    public function create_instance($entity ) {
        return new Mixtape_Model( $this, $entity );
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

    function field() {
        return new Mixtape_Model_Field_DeclarationBuilder();
    }

    function meta_field() {
        return $this->field()->of_type( Mixtape_Model_Field_Types::META );
    }

    function derived_field() {
        return $this->field()->of_type( Mixtape_Model_Field_Types::DERIVED );
    }

    public function get_delegate() {
        return $this->delegate;
    }


    /**
     * @param WP_REST_Request $request
     * @return Mixtape_Model
     * @internal Mixtape_Model_Field_Declaration $field
     */
    public function new_from_request( $request )
    {
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
        $results = array();
        foreach ($this->get_entities() as $entity) {
            $results[] = $this->create_instance($entity);
        }
        return new Mixtape_Model_Collection( $results );
    }

    public function find_one_by_id( $id) {
        $entity = $this->get_data_store()->get_entity( $id );
        return !empty( $entity ) ? $this->create_instance( $entity ) : null;
    }

    /**
     * @param $id the unique_id
     * @throws Mixtape_Exception
     * return Mixtape_Model|null
     */
    public function get_entity( $id) {
        return $this->get_data_store()->get_entity( $id );
    }

    /**
     * @throws Mixtape_Exception
     * @return array
     */
    public function get_entities() {
        return $this->get_data_store()->get_entities();
    }
}