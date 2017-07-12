<?php
/**
 * Model Testcase
 *
 * @package MT/Testing
 */

/**
 * Class MT_Testing_Model_TestCase
 *
 * @package MT/Testing
 */
class MT_Testing_Model_TestCase extends MT_Testing_TestCase {

	/**
	 * Environment
	 *
	 * @var MT_Environment
	 */
	protected $environment;

	/**
	 * Bootstrap
	 *
	 * @var MT_Bootstrap
	 */
	protected $mixtape;

	/**
	 * Setup
	 */
	function setUp() {
		parent::setUp();
		$this->mixtape = MT_Bootstrap::create()->load();
		$this->environment = $this->mixtape->environment();
	}

	/**
	 * Expect a model is valid
	 *
	 * @param MT_Interfaces_Model $model The model.
	 */
	function assertModelValid( $model ) {
		$this->assertTrue( $model->validate() );
	}
}
