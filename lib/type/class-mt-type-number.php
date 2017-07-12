<?php
/**
 * The Number Type (a floating point type)
 *
 * @package MT/Types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Type_Number
 */
class MT_Type_Number extends MT_Type {

	/**
	 * MT_Type_Number constructor.
	 */
	function __construct() {
		parent::__construct( 'number' );
	}

	/**
	 * The default value
	 *
	 * @return float
	 */
	function default_value() {
		return 0.0;
	}

	/**
	 * Cast
	 *
	 * @param mixed $value The thing to cast.
	 * @return float
	 */
	function cast( $value ) {
		if ( ! is_numeric( $value ) ) {
			return $this->default_value();
		}
		return floatval( $value );
	}

	/**
	 * Sanitize
	 *
	 * @param mixed $value The value to sanitize.
	 * @return float
	 */
	function sanitize( $value ) {
		return $this->cast( $value );
	}
}
