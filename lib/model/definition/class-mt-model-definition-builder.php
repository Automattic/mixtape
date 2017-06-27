<?php
/**
 * Definition Builder
 *
 * @package MT/Model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Model_Definition_Builder
 */
class MT_Model_Definition_Builder implements MT_Interfaces_Builder {
	/**
	 * Declaration
	 *
	 * @var MT_Interfaces_Model_Declaration
	 */
	private $declaration;
	/**
	 * Data Store
	 *
	 * @var MT_Interfaces_Data_Store
	 */
	private $data_store;
	/**
	 * Environment
	 *
	 * @var MT_Environment
	 */
	private $environment;
	/**
	 * Permissions Provider
	 *
	 * @var MT_Interfaces_Permissions_Provider
	 */
	private $permissions_provider;

	/**
	 * MT_Model_Definition_Builder constructor.
	 */
	function __construct() {
		$this->with_data_store( new MT_Data_Store_Nil() )
			->with_permissions_provider( new MT_Permissions_Any() );
	}

	/**
	 * With Declaration
	 *
	 * @param MT_Interfaces_Model_Declaration|MT_Interfaces_Permissions_Provider $declaration D.
	 * @return MT_Model_Definition_Builder
	 */
	function with_declaration( $declaration ) {
		if ( is_string( $declaration ) && class_exists( $declaration ) ) {
			$declaration = new $declaration();
		}
		MT_Expect::is_a( $declaration, 'MT_Interfaces_Model_Declaration' );
		$this->declaration = $declaration;
		if ( is_a( $declaration, 'MT_Interfaces_Permissions_Provider' ) ) {
			$this->with_permissions_provider( $declaration );
		}
		return $this;
	}

	/**
	 * With Data Store
	 *
	 * @param null|MT_Interfaces_Builder $data_store
	 * @return MT_Model_Definition_Builder $this
	 */
	function with_data_store( $data_store = null ) {
		$this->data_store = $data_store;
		return $this;
	}

	/**
	 * @param MT_Interfaces_Permissions_Provider $permissions_provider
	 */
	function with_permissions_provider( $permissions_provider ) {
		$this->permissions_provider = $permissions_provider;
	}

	/**
	 * @param MT_Environment $environment
	 * @return MT_Model_Definition_Builder $this
	 */
	function with_environment( $environment ) {
		$this->environment = $environment;
		return $this;
	}

	/**
	 * @return MT_Model_Definition
	 */
	function build() {
		return new MT_Model_Definition( $this->environment, $this->declaration, $this->data_store, $this->permissions_provider );
	}
}
