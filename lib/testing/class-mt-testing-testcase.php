<?php

/**
 * Class MT_Testing_TestCase
 */
class MT_Testing_TestCase extends WP_UnitTestCase {

	/**
	 * Expect a class Exists.
	 *
	 * @param string $cls Class Name.
	 */
	function assertClassExists( $cls ) {
		return $this->assertTrue( class_exists( $cls ), 'Failed Asserting that class ' . $cls . ' exists.' );
	}
}
