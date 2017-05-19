<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Environment {
    /**
     * @var array
     */
    protected $rest_api_bundles;
    /**
     * @var array
     */
    protected $model_definitions;
    /**
     * @var bool
     */
    private $started;
    /**
     * @var Mixtape_Bootstrap
     */
    private $bootstrap;
    /**
     * @var Mixtape_Type_Registry
     */
    private $type_registry;
    /**
     * @var Mixtape_FluentInterface_Define
     */
    private $definer;
    /**
     * @var Mixtape_FluentInterface_Get
     */
    private $getter;

    /**
     * @var array
     */
    private $pending_definitions;

    /**
     * Mixtape_Environment constructor.
     * @param Mixtape_Bootstrap $bootstrap
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
     * @param string|Mixtape_Interfaces_Model_Declaration $delegate
     * @param null|Mixtape_Interfaces_Data_Store $data_store
     * @return $this
     * @throws Mixtape_Exception
     */
    public function define_model( $delegate, $data_store = null ) {
        $this->define()
            ->model()
            ->with_environment( $this )
            ->with_data_store( $data_store )
            ->with_declaration( $delegate );
        return $this;
    }

    /**
     * @param string $where
     * @param Mixtape_Interfaces_Builder $builder
     * @return Mixtape_Environment $this
     * @throws Mixtape_Exception
     */
    public function push_builder( $where, $builder ) {
        Mixtape_Expect::is_a( $builder, 'Mixtape_Interfaces_Builder' );
        $this->pending_definitions[$where][] = $builder;
        return $this;
    }

    /**
     * @param string $class the class name
     * @return Mixtape_Model_Definition the definition
     * @throws Mixtape_Exception
     */
    public function model_definition( $class ) {
        if ( ! class_exists( $class ) ) {
            throw new Mixtape_Exception( $class . ' does not exist' );
        }
        $this->load_pending_builders( 'models' );
        Mixtape_Expect::that( isset( $this->model_definitions[$class] ), $class . ' definition does not exist' );
        return $this->model_definitions[$class];
    }

    private function load_pending_builders( $type ) {
        foreach ( $this->pending_definitions[$type] as $pending ) {
            /** @var Mixtape_Interfaces_Builder $pending */
            if ( 'models' === $type ) {
                $this->add_model_definition( $pending->build() );
            }
            if ( 'bundles' === $type ) {
                $this->add_rest_bundle( $pending->build() );
            }
        }
    }

    /**
     * @param Mixtape_Interfaces_Rest_Api_Controller_Bundle $bundle
     * @return Mixtape_Environment $this
     */
    private function add_rest_bundle( $bundle ) {
        $key = $bundle->get_bundle_prefix();
        $this->rest_api_bundles[ $key ] = $bundle;
        return $this;
    }

    /**
     * Start things up
     * @return $this
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

    public function get_bootstrap() {
        return $this->bootstrap;
    }

    public function full_class_name( $partial_name ) {
        return $this->get_bootstrap()
            ->class_loader()
            ->prefixed_class_name( $partial_name );
    }

    public function endpoint( $class ) {
        $builder = new Mixtape_Rest_Api_Controller_Builder();
        return $builder->with_class($class)->with_environment($this);
    }

    /**
     * @return Mixtape_FluentInterface_Define
     */
    public function define() {
        return $this->definer;
    }

    /**
     * @return Mixtape_FluentInterface_Get
     */
    public function get() {
        return $this->getter;
    }

    /**
     * @return Mixtape_Type_Registry
     */
    public function type() {
        return $this->type_registry;
    }

    /**
     * @return Mixtape_Data_Store_Builder
     */
    public function data_store() {
        return new Mixtape_Data_Store_Builder();
    }

    /**
     * @param Mixtape_Model_Definition $definition
     * @return Mixtape_Environment $this
     */
    private function add_model_definition( $definition ) {
        $key = $definition->get_model_class();
        $this->model_definitions[$key] = $definition;
        return $this;
    }
}