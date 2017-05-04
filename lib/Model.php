<?php

/**
 * Class Mixtape_Model
 */
class Mixtape_Model implements Mixtape_Interfaces_Model {

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array|int|null|WP_Comment|WP_Post|WP_User
     */
    protected $raw_data;

    /**
     * @var array the model fields Mixtape_Model_Field_Declaration
     */
    protected $fields;
    /**
     * @var Mixtape_Model_Definition
     */
    private $definition;
    /**
     * @var Mixtape_Interfaces_Model_Delegate
     */
    private $delegate;

    /**
     * Mixtape_Model constructor.
     * @param Mixtape_Model_Definition $definition
     * @param array|int|WP_Post|WP_Comment|WP_User $data the data object. either an int id, a wp entity
     * @throws Mixtape_Exception
     */
    function __construct( $definition, $data = array() ) {
        $this->definition = $definition;
        $this->delegate = $this->definition->get_delegate();
        $this->fields = $this->definition->get_field_declarations();
        $this->data = array();

        if ( is_array( $data ) ) {
            $model_data = $data;
        } else {
            $model_data = $this->get_data_array_from_entity( $data );
        }

        $this->raw_data = $model_data;

        $post_array_keys = array_keys( $model_data );
        foreach ( $this->fields as $key => $field_declaration ) {
            // eager load anything that is not a meta or derived field
            if ( false === $field_declaration->is_field() ) {
                continue;
            }
            $this->add_data_for_key( $field_declaration, $post_array_keys, $model_data, $key);
        }
    }

    protected function get_data_array_from_entity( $entity ) {
        if ( is_numeric( $entity  ) ) {
            $data_store = $this->definition->get_data_store();
            return $data_store->get_entity( $entity );
        } else if ( is_a( $entity, 'WP_Post' ) ) {
            return $entity->to_array();
        } else {
            throw new Mixtape_Exception('does not understand entity');
        }
    }

    public function get( $field_name ) {
        if ( !isset( $this->fields[$field_name] ) ) {
            return null;
        }
        $field_declaration = $this->fields[$field_name];
        $this->load_field_value_if_missing( $field_declaration );
        return $this->prepare_value( $field_declaration );
    }
    
    public function set( $field, $value ) {
        if (!isset( $this->fields[$field] ) ) {
            return $this;
        }

        $field_declaration = $this->fields[$field];
        $val = $field_declaration->cast_value( $value );
        $this->data[$field_declaration->name] = $val;
        return $this;
    }

    /**
     * @param $other WP_REST_Request
     * @return Mixtape_Model
     */
    public function merge_updates_from_request( $request, $updating = false ) {
        $fields = $this->definition->get_field_declarations();
        $field_data = array();
        foreach ( $fields as $field ) {
            if ( $field->is_derived_field() ) {
                continue;
            }
            if ( isset( $request[$field->name] ) && !( $updating && $field->primary ) ) {
                $field_data[ $field->name ] = $request[$field->name];
                $this->set( $field->name, $request[$field->name] );
            }
        }
        return $this;
    }


    /**
     * @param $field_declaration Mixtape_Model_Field_Declaration
     * @param $post_array_keys array
     * @param $model_data array
     * @param $key string
     */
    protected function add_data_for_key( $field_declaration, $post_array_keys, $model_data, $key ) {
        $map_from = $field_declaration->get_name_to_map_from();
        if (in_array($map_from, $post_array_keys)) {
            $this->set( $key, $model_data[$map_from] );
        } else if (in_array($key, $post_array_keys)) {
            $this->set( $key, $model_data[$key] );
        } else {
            $this->set( $key, $field_declaration->get_default_value() );
        }
    }

    /**
     * @param Sensei_Domain_Models_Field_Declaration $field_declaration
     */
    protected function load_field_value_if_missing( $field_declaration ) {
        $field_name = $field_declaration->name;
        if ( !isset( $this->data[ $field_name ] ) ) {
            if ( $field_declaration->is_meta_field() ) {
                $value = $this->get_data_store()->get_meta_field_value( $this, $field_declaration );
                $this->set( $field_name, $value );
            } else if ( $field_declaration->is_derived_field() ) {
                $map_from = $field_declaration->get_name_to_map_from();
                $value = $this->delegate->call( $map_from, $this );
                $this->set( $field_name, $value );
            } else {
                // load the default value for the field
                $this->set( $field_name, $field_declaration->get_default_value() );
            }
        }
    }

    public function upsert() {
        $fields = $this->map_field_types_for_upserting( Mixtape_Model_Field_Types::FIELD );
        $meta_fields = $this->map_field_types_for_upserting( Mixtape_Model_Field_Types::META );
        return $this->get_data_store()->upsert( $this, $fields, $meta_fields );
    }

    public function delete() {
        return $this->get_data_store()->delete( $this );
    }

    public function get_data_transfer_object_field_mappings() {
        $mappings = array();
        foreach ( $this->get_field_declarations() as $field_declaration ) {
            if ( !$field_declaration->suppports_output_type( 'json' ) ) {
                continue;
            }
            $mappings[$field_declaration->json_name] = $field_declaration->name;
        }
        return $mappings;
    }

    private function map_field_types_for_upserting( $field_type ) {
        $field_values_to_insert = array();
        foreach ( $this->get_field_declarations( $field_type ) as $field_declaration ) {
            $what_to_map_to = $field_declaration->get_name_to_map_from();
            $field_values_to_insert[$what_to_map_to] = $this->get( $field_declaration->name );
        }
        return $field_values_to_insert;
    }

    public function get_id() {
        return $this->delegate->call( 'get_id', $this );
    }

    public function validate() {
        $validation_errors = array();

        foreach ( $this->fields as $key => $field_declaration ) {
            $is_valid = $this->run_field_validations( $field_declaration );
            if ( is_wp_error( $is_valid ) ) {
                $validation_errors[] = $is_valid->get_error_data();
            }
        }
        if ( count( $validation_errors ) > 0 ) {
            return $this->validation_error( $validation_errors );
        }
        return true;
    }

    /**
     * @param $field_declaration Sensei_Domain_Models_Field_Declaration
     * @return bool|WP_Error
     */
    protected function run_field_validations( $field_declaration ) {
        if ( $field_declaration->is_derived_field() ) {
            return true;
        }
        $value = $this->get( $field_declaration->name );
        if ( $field_declaration->required && empty( $value ) ) {
            return new WP_Error(
                'required-field-empty',
                sprintf( __( '%s cannot be empty', 'woothemes-sensei' ), $field_declaration->name )
            );
        } else if ( !$field_declaration->required && ! empty( $value ) ) {
            foreach ( $field_declaration->validations as $method_name ) {
                $result = $this->delegate->call( $method_name, $this, array( $value ) );
                if ( is_wp_error( $result ) ) {
                    $result->add_data(array(
                        'reason' => $result->get_error_messages(),
                        'field' => $field_declaration->name,
                        'value' => $value ) );
                    return $result;
                }
            }
        }
        return true;
    }

    function get_field_declarations( $filter_by_type=null ) {
        return $this->definition->get_field_declarations( $filter_by_type );
    }

    protected function as_bool($value ) {
        return (bool)$value;
    }

    protected function as_uint($value ) {
        return absint( $value );
    }

    protected function as_nullable_uint( $value ) {
        return ( empty( $value ) && !is_numeric( $value ) ) ? null : $this->as_uint( $value );
    }

    /**
     * @param $data
     * @param $field_declaration Sensei_Domain_Models_Field_Declaration
     * @return mixed|null
     */
    private function prepare_value( $field_declaration ) {
        $value = $this->data[ $field_declaration->name ];

        if ( isset( $field_declaration->before_return ) && !empty( $field_declaration->before_return ) ) {
            return $this->delegate->call( $field_declaration->before_return, $this, array( $value ) );
        }

        return $value;
    }

    /**
     * @return Mixtape_Interfaces_Data_Store
     * @throws Mixtape_Exception
     */
    protected function get_data_store()
    {
        return $this->definition->get_data_store();
    }
}