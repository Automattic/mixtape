<?php
/**
 * Model type
 *
 * @package Mixtape/Type
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MT_Type_Model
 */
class MT_Type_Model extends MT_Type {
    /**
     * The Class (must implement MT_Interfaces_Model).
     *
     * @var string
     */
    private $model_class;

    /**
     * MT_Type_Array constructor.
     *
     * @param string $model_class The model class.
     */
    public function __construct( $model_class = 'MT_Model' ) {
        MT_Expect::implements_interface( $model_class, 'MT_Interfaces_Model' );
        $this->model_class = $model_class;
        parent::__construct( 'model:' . $model_class );
    }

    /**
     * Get default MT_Interfaces_Model
     *
     * @return MT_Interfaces_Model
     */
    public function default_value() {
        $klass = $this->model_class;
        return new $klass();
    }

    /**
     * Sanitize.
     *
     * @param MT_Interfaces_Model|mixed $value Val.
     * @return MT_Interfaces_Model
     * @throws MT_Exception if value not a $this->model_class.
     */
    function sanitize( $value ) {
        if ( is_a( $value, $this->model_class ) ) {
            return $value->sanitize();
        }
        throw new MT_Exception( 'MT_Type_Model: don\'t know how to sanitize provided value' );
    }

    /**
     * Cast to MT_Interfaces_Model if possible
     *
     * @param MT_Interfaces_Model|array $value the value. Should be either array or typeclass
     * @return MT_Interfaces_Model
     * @throws MT_Exception if value not an array or a $this->model_class.
     */
    public function cast( $value ) {
        if ( is_a( $value, $this->model_class ) ) {
            return $value;
        } else if ( is_array( $value ) ) {
            $klass = $this->model_class;
            return new $klass( $value );
        }
        throw new MT_Exception( 'MT_Type_Model: don\'t know how to cast provided value' );
    }
}