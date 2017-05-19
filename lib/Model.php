<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
     * @var Mixtape_Interfaces_Model_Declaration
     */
    private $declaration;

    /**
     * Mixtape_Model constructor.
     * @param Mixtape_Model_Definition $definition
     * @param array|int|WP_Post|WP_Comment|WP_User $data the data object. either an int id, a wp entity
     * @throws Mixtape_Exception
     */
    function __construct( $definition, $data = array() ) {
        Mixtape_Expect::that( is_array( $data ), '$data should be an array' );

        $this->definition = $definition;
        $this->declaration = $this->definition->get_model_declaration();
        $this->fields = $this->definition->get_field_declarations();
        $this->data = array();

        $this->raw_data = $data;
        $data_keys = array_keys( $data );

        foreach ( $data_keys as $key ) {
            $this->set( $key, $this->raw_data[$key] );
        }
    }

    public function get( $field_name, $args = array() ) {
        Mixtape_Expect::that( $this->has( $field_name ), 'Field ' . $field_name . 'is not defined' );
        $field_declaration = $this->fields[$field_name];

        if ( ! isset( $this->data[ $field_name ] ) ) {
            if ( $field_declaration->is_derived_field() ) {
                /** @var Mixtape_Model_Field_Declaration $field_declaration */
                $map_from = $field_declaration->get_map_from();
                $value = $this->declaration->call( $map_from, array( $this ) );
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
        Mixtape_Expect::that( $this->has( $field ), 'Field ' . $field . 'is not defined' );
        /** @var Mixtape_Model_Field_Declaration $field_declaration */
        $field_declaration = $this->fields[$field];
        if ( null !== $field_declaration->before_model_set() ) {
            $val = $this->declaration->call( $field_declaration->before_model_set(), array( $this, $value ) );
        } else {
            $val = $field_declaration->cast_value( $value );
        }
        $this->data[$field_declaration->get_name()] = $val;
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

    public function get_id() {
        return $this->declaration->get_id( $this );
    }

    public function set_id( $id ) {
        return $this->declaration->set_id( $this, $id );
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

    public function sanitize() {
        foreach ( $this->fields as $key => $field_declaration ) {
            $field_name = $field_declaration->get_name();
            $value = $this->get( $field_name );
            $is_valid = $this->run_field_validations( $field_declaration );
            if ( is_wp_error( $is_valid ) ) {
                $validation_errors[] = $is_valid->get_error_data();
            }
            $custom_sanitization = $field_declaration->get_sanitize();
            if ( ! empty( $custom_sanitization ) ) {
                $value = $this->declaration->call( $custom_sanitization, array( $this, $value ) );
            } else {
                $value = $field_declaration->get_type_definition()->sanitize( $value );
            }
            $this->set( $field_name, $value );
        }
        return $this;
    }

    /**
     * @param $error_data array
     * @return WP_Error
     */
    protected function validation_error( $error_data ) {
        return new WP_Error( 'validation-error', 'validation-error', $error_data );
    }

    /**
     * @param $field_declaration Mixtape_Model_Field_Declaration
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
                sprintf( __( '%s cannot be empty', 'mixtape' ), $field_declaration->get_name() )
            );
        } else if ( !$field_declaration->is_required() && ! empty( $value ) ) {
            $validation_data = new Mixtape_Model_ValidationData( $value, $this, $field_declaration );
            foreach ( $field_declaration->get_validations() as $validation ) {
                $result = $this->declaration->call( $validation, array( $validation_data ) );
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
    private function prepare_value( $field_declaration ) {
        $key = $field_declaration->get_name();
        $value = $this->data[ $key ];
        $before_return = $field_declaration->get_before_return();
        if ( isset( $before_return ) && !empty( $before_return ) ) {
            $value = $this->declaration->call( $before_return, array( $this, $key, $value ) );
        }

        return $value;
    }
}
