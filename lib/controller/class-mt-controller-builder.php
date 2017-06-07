<?php
/**
 * Build A conroller
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Builder
 */
class MT_Controller_Builder implements MT_Interfaces_Builder {

	/**
	 * Class.
	 *
	 * @var string
	 */
	private $controller_class;
	/**
	 * Environment
	 *
	 * @var MT_Environment
	 */
	private $environment;
	/**
	 * Bundle
	 *
	 * @var MT_Controller_Bundle
	 */
	private $bundle;
	/**
	 * Definition.
	 *
	 * @var null|MT_Model_Definition
	 */
	private $model_definition = null;
	/**
	 * Base
	 *
	 * @var string
	 */
	private $base = '';
	/**
	 * Actions
	 *
	 * @var array
	 */
	private $actions = array();

	/**
	 * MT_Controller_Builder constructor.
	 */
	function __construct() {
	}

	/**
	 * For Model
	 *
	 * @param MT_Model_Definition $definition Def.
	 * @return MT_Controller_Builder $this
	 */
	function for_model( $definition ) {
		$this->model_definition = $definition;
		return $this;
	}

	/**
	 * With Bundle
	 *
	 * @param MT_Controller_Bundle $bundle Bundle.
	 * @return MT_Controller_Builder $this
	 */
	public function with_bundle( $bundle ) {
		$this->bundle = $bundle;
		return $this;
	}

	/**
	 * With Environment
	 *
	 * @param MT_Environment $env Environment.
	 * @return MT_Controller_Builder $this
	 */
	public function with_environment( $env ) {
		$this->environment = $env;
		return $this;
	}

	/**
	 * With Controller Class
	 *
	 * @param string $controller_class Class.
	 *
	 * @return MT_Controller_Builder $this
	 * @throws MT_Exception When clas invalid.
	 */
	public function with_class( $controller_class ) {
		MT_Expect::that( class_exists( $controller_class ), 'class ' . $controller_class . ' does not exist' );
		$this->controller_class = $controller_class;
		return $this;
	}

	/**
	 * Setup a crud controller.
	 *
	 * @param null|string $base The base.
	 * @return MT_Controller_Builder $this
	 */
	public function crud( $base = null ) {
		if ( $base ) {
			$this->with_base( $base );
		}
		return $this->with_class( 'MT_Controller_CRUD' );
	}

	/**
	 * Setup a settings controller.
	 *
	 * @return MT_Controller_Builder $this
	 */
	public function settings() {
		return $this->with_class( 'MT_Controller_Settings' );
	}

	/**
	 * Build a controller.
	 *
	 * @return MT_Controller
	 */
	public function build() {
		$controller_class = $this->controller_class;
		if ( $this->model_definition ) {
			return new $controller_class( $this->bundle, $this->base, $this->model_definition );
		}
		return new $controller_class( $this->bundle, $this->environment );
	}

	/**
	 * Actions.
	 *
	 * @param array $actions Actions.
	 * @return $this
	 */
	public function with_actions( $actions ) {
		$this->actions = $actions;
		return $this;
	}

	/**
	 * Base
	 *
	 * @param string $base Base.
	 * @return $this
	 */
	public function with_base( $base ) {
		$this->base = $base;
		return $this;
	}
}
