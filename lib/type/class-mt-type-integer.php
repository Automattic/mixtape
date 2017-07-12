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
 * Class MT_Type_Integer
 */
class MT_Type_Integer extends MT_Type {

	/**
	 * Is this unsigned?
	 *
	 * @var bool
	 */
	private $unsigned;

	/**
	 * MT_Type_Integer constructor.
	 *
	 * @param bool $unsigned Unsigned.
	 */
	public function __construct( $unsigned = false ) {
		$this->unsigned = $unsigned;
		parent::__construct( 'integer' );
	}

	/**
	 * Default
	 *
	 * @return int
	 */
	public function default_value() {
		return 0;
	}

	/**
	 * Cast
	 *
	 * @param mixed $value Val.
	 * @return int
	 */
	public function cast( $value ) {
		if ( ! is_numeric( $value ) ) {
			return $this->default_value();
		}
		return $this->unsigned ? absint( $value ) : intval( $value, 10 );
	}

	/**
	 * Sanitize
	 *
	 * @param mixed $value Val.
	 * @return int
	 */
	function sanitize( $value ) {
		return $this->cast( $value );
	}
}
