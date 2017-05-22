<?php
/**
 * The FluentInterface Define
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_FluentInterface_Define
 */
class Mixtape_FluentInterface_Define {
	/**
	 * The environment.
	 *
	 * @var Mixtape_Environment
	 */
	private $environment;

	/**
	 * Mixtape_FluentInterface_Define constructor.
	 *
	 * @param Mixtape_Environment $environment The Environment.
	 */
	function __construct( $environment ) {
		$this->environment = $environment;
	}

	/**
	 * Define a new Model
	 *
	 * @param null|Mixtape_Interfaces_Model_Declaration $declaration Possibly a declaration.
	 *
	 * @return Mixtape_Model_Definition_Builder
	 */
	function model( $declaration = null ) {
		$builder = new Mixtape_Model_Definition_Builder();
		if ( null !== $declaration ) {
			$builder->with_declaration( $declaration );
		}
		$this->environment->push_builder( 'models', $builder->with_environment( $this->environment ) );
		return $builder;
	}

	/**
	 * Define a new DataStore.
	 *
	 * @return Mixtape_Data_Store_Builder
	 */
	public function data_store() {
		return new Mixtape_Data_Store_Builder();
	}

	/**
	 * Define a new Type
	 *
	 * @param string                  $identifier The type name.
	 * @param Mixtape_Interfaces_Type $instance The type.
	 *
	 * @return Mixtape_Type_Registry
	 * @throws Mixtape_Exception Throw in case $instance isn't a Mixtape_Interfaces_Type.
	 */
	function type( $identifier, $instance ) {
		return $this->environment->type()->define( $identifier, $instance );
	}

	/**
	 * Define a new REST API Bundle.
	 *
	 * @param null|string|Mixtape_Interfaces_Rest_Api_Controller_Bundle $maybe_bundle_or_prefix The bundle name.
	 * @return Mixtape_Rest_Api_Controller_Bundle_Builder
	 */
	function rest_api( $maybe_bundle_or_prefix = null ) {
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
