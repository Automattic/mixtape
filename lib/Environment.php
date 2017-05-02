<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Environment {

    protected $data_stores;
    protected $rest_api_bundles;
    protected $field_declarations_by_model;
    private $started;
    /**
     * @var Mixtape
     */
    private $main;

    /**
     * Mixtape_Environment constructor.
     * @param $mixtape Mixtape
     */
    public function __construct( $main) {
        $this->main = $main;
        $this->started = false;
        $this->field_declarations_by_model = array();
        $this->factories = array();
        $this->data_stores = array();
        $this->rest_api_bundles = array();
    }

    public function get_factory( $klass ) {
        $klass = is_string( $klass ) ? $klass : get_class( $klass );
        $this->get_field_declarations( $klass );
        if ( !isset( $this->factories[$klass] ) ) {
            $this->factories[$klass] = new Mixtape_Model_Factory ($klass, $this );
        }

        return $this->factories[$klass];
    }

    /**
     * @param $request
     * @return Mixtape_Model
     */
    public function new_from_request( $klass, $request )
    {
        $fields = $this->get_field_declarations( $klass );
        $field_data = array();
        foreach ($fields as $field) {
            if (isset($request[$field->name])) {
                $field_data[$field->name] = $request[$field->name];
            } else {
                $field_data[$field->name] = $field->get_default_value();
            }
        }

        return $this->create_object( $klass, $field_data );
    }

    public function all( $klass ) {
        $results = array();
        foreach ( $this->get_entities( $klass ) as $entity ) {
            $results[] = $this->create_object( $klass, $entity );
        }
        return new Mixtape_Model_Collection( $results );
    }

    public function find_one_by_id( $klass, $id) {
        $entity = $this->get_entity( $klass, $id );
        return !empty($entity) ? $this->create_object( $klass, $entity ) : null;
    }

    /**
     * @param $id unique id
     * @throws Mixtape_Exception
     * return object|null
     */
    public function get_entity( $klass, $id) {
        return $this->call_fn( $klass, 'get_entity', $id);
    }

    /**
     * @throws Mixtape_Exception
     * @return array
     */
    public function get_entities( $klass ) {
        return $this->call_fn( $klass, 'get_entities' );
    }

    private function call_fn() {
        $args = func_get_args();
        $klass = array_shift( $args );
        $fn_name = array_shift( $args );
        return call_user_func_array( array( $this->get_domain_model_class( $klass ), $fn_name ), $args );
    }

    public function create_object( $klass, $entity) {
        $klass = $this->get_domain_model_class( $klass );
        return new $klass( $entity );
    }

    private function get_domain_model_class( $thing ) {
        $thing = $this->force_string( $thing );

        $this->get_field_declarations( $thing );

        if (!in_array( $thing, array_keys( $this->field_declarations_by_model ))) {
            throw new Mixtape_Exception( 'Model not registered: ' . $thing );
        }
        return $thing;
    }

    public function get_field_declarations( $klass, $filter_by_type=null ) {
        $super = $this->main->class_loader()->prefixed_class_name( 'Model' );
        if ( !is_subclass_of( $klass, $super ) ) {
            throw new Mixtape_Exception( $klass . ' is not a subclass of ' . $super );
        }

        if ( !isset($this->field_declarations_by_model[$klass]) ||
            null === $this->field_declarations_by_model[$klass] ) {
            // lazy-load model declarations when the first model if this type is constructed
            $fields = call_user_func( array( $klass, 'declare_fields' ) );
            $this->field_declarations_by_model[$klass] = call_user_func( array( $klass, 'initialize_field_map' ), $fields );
        }
        if ( null === $filter_by_type ) {
            return $this->field_declarations_by_model[$klass];
        }
        $filtered = array();
        foreach ($this->field_declarations_by_model[$klass] as $field_declaration ) {
            if ( $field_declaration->type === $filter_by_type ) {
                $filtered[] = $field_declaration;
            }
        }
        return $filtered;
    }

    /**
     * @param string|object $key
     * @return Mixtape_Interfaces_Data_Store
     * @throws Mixtape_Exception
     */
    public function get_data_store( $key ) {
        $key = $this->force_string( $key );
        if (!isset( $this->data_stores[$key] ) ) {
            throw new Mixtape_Exception( 'No datastore set for ' . $key );
        }
        return $this->data_stores[ $key ];
    }

    /**
     * @param $name string the name
     * @param $data_store_instance Mixtape_Interfaces_Data_Store
     * @return $this
     */
    public function set_data_store( $name, $data_store_instance ) {
        $this->data_stores[$name] = $data_store_instance;
        return $this;
    }

    /**
     * @param object|string $thing
     * @return string
     */
    private function force_string( $thing ) {
        if ( ! is_string( $thing ) ) {
            return get_class( $thing );
        }
        return $thing;
    }

    /**
     * @param $bundle Mixtape_Rest_Api_Controller_Bundle
     * @return $this Mixtape_Environment
     */
    public function add_rest_bundle( $bundle ) {
        $key = $bundle->get_bundle_prefix();
        $this->rest_api_bundles[ $key ] = $bundle;
        return $this;
    }

    /**
     * Start things up
     * @return $this
     */
    public function start() {
        if ( false === $this->started ) {
            do_action( 'mixtape_environment_before_start', $this );
            foreach ( $this->rest_api_bundles as $k => $bundle ) {
                $bundle->start();
            }
            $this->started = true;
            do_action( 'mixtape_environment_after_start', $this );
        }

        return $this;
    }
}