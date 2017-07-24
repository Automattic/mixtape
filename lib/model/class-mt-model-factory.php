<?php
/**
 * The Model Factory
 *
 * @package Mixtape/Model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Model_Factory
 */
class MT_Model_Factory {
	/**
	 * The Environment
	 *
	 * @var MT_Environment
	 */
	private $environment;
	/**
	 * The Class Name
	 *
	 * @var string
	 */
	private $class_name;
	/**
	 * The Data Store
	 *
	 * @var MT_Interfaces_Data_Store
	 */
	private $data_store;
	/**
	 * Permissions Provider
	 *
	 * @var MT_Interfaces_Permissions_Provider
	 */
	private $permissions_provider;

	/**
	 * MT_Model_Factory constructor.
	 *
	 * @param MT_Environment           $environment Environment.
	 * @param string                   $class_name Class Name.
	 * @param MT_Interfaces_Data_Store $data_store Data Store.
	 */
	public function __construct( $environment, $class_name, $data_store ) {
		$this->with_model( $class_name );
		$this->with_environment( $environment );
		$this->with_data_store( $data_store );
	}

	/**
	 * With Environment
	 *
	 * @param MT_Environment $environment Env.
	 *
	 * @return MT_Model_Factory $this
	 */
	public function with_environment( $environment ) {
		$this->environment = $environment;
		$this->call_static_model_method( 'with_environment', array( $this->environment ) );
		return $this;
	}

	/**
	 * With Model
	 *
	 * @param string $class_name Class Name.
	 *
	 * @return MT_Model_Factory $this
	 */
	public function with_model( $class_name ) {
		$this->class_name = $class_name;
		return $this;
	}

	/**
	 * With Data Store
	 *
	 * @param MT_Interfaces_Data_Store $data_store Data Store.
	 *
	 * @return MT_Model_Factory $this
	 */
	public function with_data_store( $data_store ) {
		$this->data_store = $data_store;
		$this->call_static_model_method( 'with_data_store', array( $this->data_store ) );
		return $this;
	}

	/**
	 * Call a static method on the model
	 *
	 * @param string $method Method to call.
	 * @param array  $args The args.
	 *
	 * @return mixed
	 */
	private function call_static_model_method( $method, $args = array() ) {
		return call_user_func_array( array( $this->class_name, $method ), $args );
	}

	/**
	 * Create a model
	 *
	 * @param array $props Data.
	 * @param array $args Args.
	 * @return MT_Interfaces_Model
	 */
	public function create( $props = array(), $args = array() ) {
		return $this->call_static_model_method( 'create', array( $props, $args ) );
	}

	/**
	 * New From Array
	 *
	 * @param array $data Data.
	 *
	 * @return MT_Interfaces_Model|WP_Error
	 */
	public function new_from_array( $data ) {
		return $this->call_static_model_method( 'new_from_array', array( $data ) );
	}

	/**
	 * Get Fields
	 *
	 * @return array
	 */
	public function get_fields() {
		return $this->call_static_model_method( 'get_fields' );
	}

	/**
	 * Get Data Store
	 *
	 * @return MT_Interfaces_Data_Store
	 */
	public function get_data_store() {
		return $this->data_store;
	}

	/**
	 * Get Name
	 *
	 * @return string
	 */
	public function get_name() {
		return strtolower( $this->class_name );
	}

	/**
	 * Permissions Check
	 *
	 * @param WP_REST_Request $request Request.
	 * @param string          $action Action.
	 * @return mixed
	 */
	public function permissions_check( $request, $action ) {
		if ( ! empty( $this->permissions_provider ) ) {
			return call_user_func_array( array( $this->permissions_provider, 'permissions_check' ), array( $request, $action ) );
		}
		return $this->call_static_model_method( 'permissions_check', array( $request, $action ) );
	}

	/**
	 * @param MT_Interfaces_Permissions_Provider $permissions_provider PP.
	 * @return $this
	 */
	public function with_permissions_provider( $permissions_provider ) {
		$this->permissions_provider = $permissions_provider;
		return $this;
	}
}
