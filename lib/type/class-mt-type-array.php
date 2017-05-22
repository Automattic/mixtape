<?php
/**
 * Array type
 *
 * @package Mixtape/Type
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Type_Array
 */
class MT_Type_Array extends MT_Type {

	/**
	 * MT_Type_Array constructor.
	 */
	public function __construct() {
		parent::__construct( 'array' );
	}

	/**
	 * Get default
	 *
	 * @return array
	 */
	public function default_value() {
		return array();
	}

	/**
	 * Cast to array
	 *
	 * @param mixed $value the value.
	 * @return array
	 */
	public function cast( $value ) {
		return (array) $value;
	}
}
