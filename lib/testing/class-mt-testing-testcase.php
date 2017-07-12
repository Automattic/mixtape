<?php
/**
 * Testcase
 *
 * @package MT/Testing
 */

/**
 * Class MT_Testing_TestCase
 */
class MT_Testing_TestCase extends WP_UnitTestCase {
	/**
	 * Admin
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Default User
	 *
	 * @var int
	 */
	protected $default_user_id;

	/**
	 * Expect a class Exists.
	 *
	 * @param string $cls Class Name.
	 */
	function assertClassExists( $cls ) {
		return $this->assertTrue( class_exists( $cls ), 'Failed Asserting that class ' . $cls . ' exists.' );
	}

	/**
	 * Setup
	 */
	function setUp() {
		parent::setUp();
		$admin = get_user_by( 'email', 'rest_api_admin_user@test.com' );
		if ( false === $admin ) {
			$this->admin_id = wp_create_user(
				'rest_api_admin_user',
				'rest_api_admin_user',
				'rest_api_admin_user@test.com'
			);
			$admin = get_user_by( 'ID', $this->admin_id );
			$admin->set_role( 'administrator' );
		}

		$this->default_user_id = get_current_user_id();
		$this->login_as_admin();
	}

	/**
	 * Login as admin
	 *
	 * @return MT_Testing_TestCase
	 */
	function login_as_admin() {
		return $this->login_as( $this->admin_id );
	}

	/**
	 * Login As User
	 *
	 * @param int $user_id User ID.
	 * @return MT_Testing_TestCase $this
	 */
	function login_as( $user_id ) {
		wp_set_current_user( $user_id );
		return $this;
	}
}
