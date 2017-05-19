<?php

class Mixtape_Rest_Api_Controller_Bundle_Definition extends Mixtape_Rest_Api_Controller_Bundle {

    private $endpoint_builders;
    /**
     * @var Mixtape_Environment
     */
    private $environment;

    function __construct( $environment, $bundle_prefix, $endpoint_builders ) {
        $this->environment = $environment;
        $this->bundle_prefix = $bundle_prefix;
        $this->endpoint_builders = $endpoint_builders;
    }

    public function get_endpoints() {
        $endpoints = array();
//        $builder_interface = $this->environment->full_class_name( 'Rest_Api_Controller_Builder' );
        foreach ( $this->endpoint_builders as $builder ) {
            /** @var Mixtape_Rest_Api_Controller_CRUD_Builder $builder */
//            if ( ! is_a( $builder, $builder_interface ) ) {
//                throw new Mixtape_Exception( get_class( $builder ) . ' is not a ' . $builder_interface );
//            }
            $endpoint = $builder->with_bundle( $this )->with_environment( $this->environment )->build();
            $endpoints[] = $endpoint;
         }
        return $endpoints;
    }
}