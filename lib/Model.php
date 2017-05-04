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

        if ( !is_array( $data ) ) {
            throw new Mixtape_Exception( '$data shoud be array' );
        }

        $this->raw_data = $data;

        $post_array_keys = array_keys( $data );
        foreach ( $this->fields as $key => $field_declaration ) {
            $this->add_data_for_key( $field_declaration, $post_array_keys, $data, $key);
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
        if ( !isset( $this->fields[$field] ) ) {
            return $this;
        }

        $field_declaration = $this->fields[$field];
        $val = $field_declaration->cast_value( $value );
        $this->data[$field_declaration->name] = $val;
        return $this;
    }

    /**
     * @param Mixtape_Model_Field_Declaration $field_declaration
     * @param array $post_array_keys
     * @param array $model_data
     * @param string $key
     * @return Mixtape_Model $this
     */
    protected function add_data_for_key( $field_declaration, $post_array_keys, $model_data, $key ) {
        if ( $field_declaration->is_derived_field() ) {
            // we lazy-load derived field values
            return $this;
        }

        if (in_array( $key, $post_array_keys ) ) {
            // simplest case: we got a $key for this, so just map it
            return $this->set( $key, $model_data[$key] );
        }

        if ( $field_declaration->is_meta_field() ) {
            // if we got here, we got a meta_field with a mapping. Lazy-loaded too
            return $this;
        }

        $map_from = $field_declaration->get_name_to_map_from();
        if (in_array( $map_from, $post_array_keys ) ) {
            return $this->set( $key, $model_data[$map_from] );
        }

        // no mapping provided for this in the entity array, just set it to a default
        return $this->set( $key, $field_declaration->get_default_value() );
    }

    /**
     * @param Mixtape_Model_Field_Declaration $field_declaration
     */
    protected function load_field_value_if_missing( $field_declaration ) {
        $field_name = $field_declaration->name;
        if ( !isset( $this->data[ $field_name ] ) ) {
            if ( $field_declaration->is_meta_field() ) {
                $value = $this->definition->get_data_store()->get_meta_field_value( $this, $field_declaration );
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

    public function get_data_transfer_object_field_mappings() {
        $mappings = array();
        foreach ( $this->definition->get_field_declarations() as $field_declaration ) {
            /** @var Mixtape_Model_Field_Declaration $field_declaration */
            if ( !$field_declaration->suppports_output_type( 'json' ) ) {
                continue;
            }
            $mappings[$field_declaration->get_data_transfer_name()] = $field_declaration->get_name();
        }
        return $mappings;
    }

    public function get_id() {
        return $this->delegate->get_id( $this );
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
     * @param $error_data array
     * @return WP_Error
     */
    protected function validation_error( $error_data ) {
        return new WP_Error( 'validation-error', 'validation-error', $error_data );
    }

    /**
     * @param $field_declaration Sensei_Domain_Models_Field_Declaration
     * @return bool|WP_Error
     */
    protected function run_field_validations( $field_declaration ) {
        if ( $field_declaration->is_derived_field() ) {
            return true;
        }
        $value = $this->get( $field_declaration->get_name() );
        if ( $field_declaration->is_required() && empty( $value ) ) {
            return new WP_Error(
                'required-field-empty',
                sprintf( __( '%s cannot be empty', 'woothemes-sensei' ), $field_declaration->get_name() )
            );
        } else if ( !$field_declaration->is_required() && ! empty( $value ) ) {
            foreach ( $field_declaration->get_validations() as $validation ) {
                $result = $this->delegate->call( $validation, $this, array( $value ) );
                if ( is_wp_error( $result ) ) {
                    $result->add_data(array(
                        'reason' => $result->get_error_messages(),
                        'field' => $field_declaration->get_name(),
                        'value' => $value ) );
                    return $result;
                }
            }
        }
        return true;
    }

    private function prepare_value( $field_declaration ) {
        $value = $this->data[ $field_declaration->name ];

        if ( isset( $field_declaration->before_return ) && !empty( $field_declaration->before_return ) ) {
            return $this->delegate->call( $field_declaration->before_return, $this, array( $value ) );
        }

        return $value;
    }
}