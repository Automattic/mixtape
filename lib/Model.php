<?php

/**
 * Class Mixtape_Model
 */
class Mixtape_Model implements Mixtape_Interfaces_Model {

    /**
     * @var array
     */
    private $data;

    /**
     * @var array|int|null|WP_Comment|WP_Post|WP_User
     */
    private $raw_data;

    /**
     * @var array the model fields Mixtape_Model_Field_Declaration
     */
    private $fields;
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
        $data_keys = array_keys($data);


        foreach ( $data_keys as $key ) {
            $this->set( $key, $this->raw_data[$key] );
        }
    }

    public function get( $field_name, $args = array() ) {
        $this->throw_if_field_unknown( $field_name );
        $field_declaration = $this->fields[$field_name];

        if ( ! isset( $this->data[ $field_name ] ) ) {
            if ( $field_declaration->is_meta_field() ) {
                $value = $this->definition
                    ->get_data_store()
                    ->get_meta_field_value( $this, $field_declaration );
                $this->set( $field_name, $value );
            } else if ( $field_declaration->is_derived_field() ) {
                $map_from = $field_declaration->get_name_to_map_from();
                $value = $this->delegate->call( $map_from, array( $this ) );
                $this->set( $field_name, $value );
            } else {
                // load the default value for the field
                $this->set( $field_name, $field_declaration->get_default_value() );
            }
        }

        $prepared_value = $this->prepare_value( $field_declaration, $args );

        return $prepared_value;
    }
    
    public function set( $field, $value ) {
        $this->throw_if_field_unknown( $field );

        $field_declaration = $this->fields[$field];
        $val = $field_declaration->cast_value( $value );
        $this->data[$field_declaration->name] = $val;
        return $this;
    }

    /**
     * Check if this model has a field
     * @param string $field
     * @return bool
     */
    public function has($field) {
        return isset( $this->fields[$field] );
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
                $result = $this->delegate->call( $validation, array( $this, $value ) );
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

    /**
     * @param Mixtape_Model_Field_Declaration $field_declaration
     * @return mixed
     */
    private function prepare_value( $field_declaration, $args = array() ) {
        $key = $field_declaration->get_name();
        $value = $this->data[ $key ];
        $before_return = $field_declaration->get_before_return();
        if ( isset( $before_return ) && !empty( $before_return ) ) {
            $value = $this->delegate->call( $before_return, array( $this, $key, $value ) );
        }

        return $value;
    }

    private function throw_if_field_unknown( $field ) {
        if ( ! $this->has( $field ) ) {
            throw new Mixtape_Exception( 'Field ' . $field . 'is not defined' );
        }
    }
}