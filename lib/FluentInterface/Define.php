<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_FluentInterface_Define {
    /**
     * @var Mixtape_Environment
     */
    private $environment;

    /**
     * Mixtape_FluentInterface_Definer constructor.
     * @param Mixtape_Environment $environment
     */
    function __construct( $environment ) {
        $this->environment = $environment;
    }

    function model( $declaration = null ) {
        $builder = new Mixtape_Model_Definition_Builder();
        if ( null !== $declaration ) {
            $builder->with_declaration( $declaration );
        }
        $this->environment->push_builder( 'models', $builder->with_environment( $this->environment ) );
        return $builder;
    }

    /**
     * @return Mixtape_Data_Store_Builder
     */
    public function data_store() {
        return new Mixtape_Data_Store_Builder();
    }

    function type( $identifier, $instance ) {
        return $this->environment->type()->define( $identifier, $instance );
    }

    /**
     * @param null|string|Mixtape_Interfaces_Rest_Api_Controller_Bundle $maybe_bundle_or_prefix
     * @return Mixtape_Rest_Api_Controller_Bundle_Builder
     */
    function rest_api($maybe_bundle_or_prefix = null ) {
        if ( is_a( $maybe_bundle_or_prefix, 'Mixtape_Interfaces_Rest_Api_Controller_Bundle' ) ) {
            $builder = new Mixtape_Rest_Api_Controller_Bundle_Builder( $maybe_bundle_or_prefix );
        } else {
            $builder = new Mixtape_Rest_Api_Controller_Bundle_Builder();
            if ( is_string( $maybe_bundle_or_prefix ) ) {
                $builder->with_prefix( $maybe_bundle_or_prefix );
            }
            $builder->with_environment( $this->environment );
        }

        $this->environment->push_builder( 'bundles', $builder );
        return $builder;
    }
}