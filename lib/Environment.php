<?php
/**
 * Environment
 *
 * Contains rest bundle, type and model definitions
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Environment
 *
 * Our global Environment
 *
 * @package Mixtape
 */
class Mixtape_Environment {

	/**
	 * This environment's registered rest bundles (versioned APIs)
	 *
	 * @var array
	 */
	protected $rest_api_bundles;

	/**
	 * This environment's model definitions
	 *
	 * @var array
	 */
	protected $model_definitions;

	/**
	 * Did this Environment start?
	 *
	 * @var bool
	 */
	private $started;

	/**
	 * Our Bootstrap
	 *
	 * @var Mixtape_Bootstrap
	 */
	private $bootstrap;

	/**
	 * Our Type Registry
	 *
	 * @var Mixtape_Type_Registry
	 */
	private $type_registry;

	/**
	 * Our Fluent Define
	 *
	 * @var Mixtape_FluentInterface_Define
	 */
	private $definer;

	/**
	 * Our Fluent Get
	 *
	 * @var Mixtape_FluentInterface_Get
	 */
	private $getter;

	/**
	 * Queues of pending builders
	 *
	 * @var array
	 */
	private $pending_definitions;

	/**
	 * Mixtape_Environment constructor.
	 *
	 * @param Mixtape_Bootstrap $bootstrap The bootstrap.
	 */
	public function __construct( $bootstrap ) {
		$this->bootstrap = $bootstrap;
		$this->started = false;
		$this->rest_api_bundles = array();
		$this->model_definitions = array();
		$this->pending_definitions = array(
			'models' => array(),
			'bundles' => array(),
		);
		$this->type_registry = new Mixtape_Type_Registry();
		$this->type_registry->initialize( $this );
		$this->definer = new Mixtape_FluentInterface_Define( $this );
		$this->getter = new Mixtape_FluentInterface_Get( $this );
	}

	/**
	 * Push a Builder to the Environment.
	 *
	 * All builders are evaluated lazily when needed
	 *
	 * @param string                     $where The queue to push the builder to.
	 * @param Mixtape_Interfaces_Builder $builder The builder to push.
	 *
	 * @return Mixtape_Environment $this
	 * @throws Mixtape_Exception In case the $builder is not a Mixtape_Interfaces_Builder.
	 */
	public function push_builder( $where, $builder ) {
		Mixtape_Expect::is_a( $builder, 'Mixtape_Interfaces_Builder' );
		$this->pending_definitions[ $where ][] = $builder;
		return $this;
	}

	/**
	 * Retrieve a previously defined Mixtape_Model_Definition
	 *
	 * @param string $class the class name.
	 * @return Mixtape_Model_Definition the definition.
	 * @throws Mixtape_Exception Throws in case the model is not registered.
	 */
	public function model_definition( $class ) {
		if ( ! class_exists( $class ) ) {
			throw new Mixtape_Exception( $class . ' does not exist' );
		}
		$this->load_pending_builders( 'models' );
		Mixtape_Expect::that( isset( $this->model_definitions[ $class ] ), $class . ' definition does not exist' );
		return $this->model_definitions[ $class ];
	}

	/**
	 * Time to build pending models and bundles
	 *
	 * @param string $type One of (models, bundles).
	 */
	private function load_pending_builders( $type ) {
		foreach ( $this->pending_definitions[ $type ] as $pending ) {
			/**
			 * Our pending builder.
			 *
			 * @var Mixtape_Interfaces_Builder $pending Our builder.
			 */
			if ( 'models' === $type ) {
				$this->add_model_definition( $pending->build() );
			}
			if ( 'bundles' === $type ) {
				$this->add_rest_bundle( $pending->build() );
			}
		}
	}

	/**
	 * Add a Bundle to our bundles (muse be Mixtape_Interfaces_Rest_Api_Controller_Bundle)
	 *
	 * @param Mixtape_Interfaces_Rest_Api_Controller_Bundle $bundle the bundle.
	 *
	 * @return Mixtape_Environment $this
	 * @throws Mixtape_Exception In case it's not a Mixtape_Interfaces_Rest_Api_Controller_Bundle.
	 */
	private function add_rest_bundle( $bundle ) {
		Mixtape_Expect::is_a( $bundle, 'Mixtape_Interfaces_Rest_Api_Controller_Bundle' );
		$key = $bundle->get_bundle_prefix();
		$this->rest_api_bundles[ $key ] = $bundle;
		return $this;
	}

	/**
	 * Start things up
	 *
	 * This should be called once our Environment is set up to our liking.
	 * Evaluates all Builders, creating missing REST Api and Model Definitions.
	 *
	 * @return Mixtape_Environment $this
	 */
	public function start() {
		if ( false === $this->started ) {
			do_action( 'mixtape_environment_before_start', $this );
			$this->load_pending_builders( 'bundles' );
			foreach ( $this->rest_api_bundles as $k => $bundle ) {
				$bundle->start();
			}
			$this->started = true;
			do_action( 'mixtape_environment_after_start', $this );
		}

		return $this;
	}

	/**
	 * Get this Environment's bootstrap instance
	 *
	 * @return Mixtape_Bootstrap our bootstrap.
	 */
	public function get_bootstrap() {
		return $this->bootstrap;
	}

	/**
	 * Build a new Endpoint
	 *
	 * @param string $class the class to use.
	 *
	 * @return $this
	 * @throws Mixtape_Exception In case our class is not compatible.
	 */
	public function endpoint( $class ) {
		$builder = new Mixtape_Rest_Api_Controller_Builder();
		return $builder->with_class( $class )->with_environment( $this );
	}

	/**
	 * Define something for this Environment
	 *
	 * @return Mixtape_FluentInterface_Define
	 */
	public function define() {
		return $this->definer;
	}

	/**
	 * Get something from the Environment
	 *
	 * @return Mixtape_FluentInterface_Get
	 */
	public function get() {
		return $this->getter;
	}

	/**
	 * Get our registered types
	 *
	 * @return Mixtape_Type_Registry
	 */
	public function type() {
		return $this->type_registry;
	}

	/**
	 * Build a new Data Store
	 *
	 * @return Mixtape_Data_Store_Builder
	 */
	public function data_store() {
		return new Mixtape_Data_Store_Builder();
	}

	/**
	 * Add a new Definition into this Environment
	 *
	 * @param Mixtape_Model_Definition $definition the definition to add.
	 * @return Mixtape_Environment $this
	 */
	private function add_model_definition( $definition ) {
		$key = $definition->get_model_class();
		$this->model_definitions[ $key ] = $definition;
		return $this;
	}
}
