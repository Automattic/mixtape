<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Class Mixtape_Model_Field_Declaration
 */
class Mixtape_Model_Field_Declaration {

    const STRING_VALUE  = 'string';
    const INT_VALUE     = 'integer';
    const ARRAY_VALUE   = 'array';
    const OBJECT_VALUE  = 'object';
    const BOOLEAN_VALUE = 'boolean';
    const ANY_VALUE     = 'any';
    const ENUM          = 'enum';

    public $before_return;
    public $before_output;
    public $map_from;
    public $type;
    public $name;
    public $primary;
    public $required;
    public $supported_outputs;
    public $description;
    public $json_name;
    public $validations;
    private $default_value;
    private $value_type;

    private $accepted_field_types = array(
        Mixtape_Model_Field_Types::FIELD,
        Mixtape_Model_Field_Types::META,
        Mixtape_Model_Field_Types::DERIVED
    );

    public function __construct( $args ) {
        if ( !isset( $args['name'] ) ) {
            throw new Mixtape_Exception( 'every field declaration should have a name' );
        }
        if ( !isset( $args['type'] ) || !in_array( $args['type'], $this->accepted_field_types ) ) {
            throw new Mixtape_Exception( 'every field should have a type (one of ' . implode( ',', $this->accepted_field_types ) . ')' );
        }
        $this->name              = $args['name'];
        $this->type              = $args['type'];
        $this->map_from          = $this->value_or_default( $args, 'map_from' );
        $this->before_return     = $this->value_or_default( $args, 'before_return' );
        $this->primary           = $this->value_or_default( $args, 'primary', false );
        $this->required          = $this->value_or_default( $args, 'required', false );
        $this->supported_outputs = $this->value_or_default( $args, 'supported_outputs', array( 'json' ) );
        $this->json_name         = $this->value_or_default( $args, 'json_name', $this->name );
        $this->value_type        = $this->value_or_default( $args, 'value_type', 'any' );
        $this->default_value     = $this->value_or_default( $args, 'default_value' );
        $this->description       = $this->value_or_default( $args, 'description', '' );
        $this->validations       = $this->value_or_default( $args, 'validations', array() );
    }

    private function value_or_default( $args, $name, $default = null ) {
        return isset( $args[$name] ) ? $args[$name] : $default;
    }

    public function is_meta_field() {
        return $this->type === Mixtape_Model_Field_Types::META;
    }

    public function is_derived_field() {
        return $this->type === Mixtape_Model_Field_Types::DERIVED;
    }

    public function is_field() {
        return $this->type === Mixtape_Model_Field_Types::FIELD;
    }

    public function get_name_to_map_from() {
        if ( isset( $this->map_from ) && !empty( $this->map_from ) ) {
            return $this->map_from;
        }

        return $this->name;
    }

    public function get_default_value() {
        if ( isset( $this->default_value ) && !empty( $this->default_value ) ) {
            return ( is_array( $this->default_value ) && is_callable( $this->default_value ) ) ? call_user_func( $this->default_value ) : $this->default_value;
        }

        if ( self::INT_VALUE === $this->value_type ) {
            return 0;
        }

        if ( self::STRING_VALUE === $this->value_type ) {
            return '';
        }

        if ( self::ARRAY_VALUE === $this->value_type ) {
            return array();
        }

        if ( self::OBJECT_VALUE === $this->value_type ) {
            return null;
        }

        return null;
    }

    public function cast_value( $value ) {
        if ( self::INT_VALUE === $this->value_type ) {
            return intval( $value, 10 );
        }

        if ( self::STRING_VALUE === $this->value_type ) {
            return '' . $value;
        }

        if ( self::ARRAY_VALUE === $this->value_type ) {
            return is_array($value) ? $value : (array)$value;
        }

        return $value;
    }

    public function supports_output_type( $type ) {
        return in_array( $type, $this->supported_outputs, true );
    }

    public function as_item_schema_property() {
        $schema = array(
            'description' => $this->description,
            'type' => $this->value_type,
            'required' => $this->required,
            'context' => array( 'view', 'edit' )
        );
        return $schema;
    }

    /**
     * @return mixed
     */
    public function get_before_output() {
        return $this->before_output;
    }

    /**
     * @return null
     */
    public function get_map_from() {
        return $this->map_from;
    }

    /**
     * @return mixed
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @return null
     */
    public function is_primary() {
        return $this->primary;
    }

    /**
     * @return null
     */
    public function is_required() {
        return $this->required;
    }

    /**
     * @return null
     */
    public function get_supported_outputs() {
        return $this->supported_outputs;
    }

    /**
     * @return null
     */
    public function get_description()
    {
        return $this->description;
    }

    /**
     * @return null
     */
    public function get_data_transfer_name()
    {
        return $this->json_name;
    }

    /**
     * @return null
     */
    public function get_validations()
    {
        return $this->validations;
    }

    public function get_before_return() {
        return $this->before_return;
    }

    public function suppports_output_type($string) {
        return in_array($string, $this->get_supported_outputs());
    }
}
