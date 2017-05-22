<?php
/**
 * Mixtape_Expect
 *
 * Asserts about invariants
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Mixtape_Expect
 */
class Mixtape_Expect {
	/**
	 * Expect a certain class
	 *
	 * @param mixed  $thing The thing to test.
	 * @param string $class_name The class.
	 *
	 * @throws Mixtape_Exception Fail if we got an unexpected class.
	 */
	static function is_a( $thing, $class_name ) {
		self::that( is_a( $thing, $class_name ), 'Expected ' . $class_name . ', got ' . get_class( $thing ) );
	}

	/**
	 * Express an invariant.
	 *
	 * @param bool   $cond The boolean condition that needs to hold.
	 * @param string $fail_message In case of failure, the reason this failed.
	 *
	 * @throws Mixtape_Exception Fail if condition doesn't hold.
	 */
	static function that( $cond, $fail_message ) {
		if ( ! $cond ) {
			throw new Mixtape_Exception( $fail_message );
		}
	}
}