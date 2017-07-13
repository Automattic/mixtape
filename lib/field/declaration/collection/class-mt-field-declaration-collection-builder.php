<?php
/**
 * Field
 *
 * @package MT/Field
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Field_Declaration_Collection_Builder
 */
class MT_Field_Declaration_Collection_Builder {
	/**
	 * Declarations
	 *
	 * @var array
	 */
	private $field_declarations;
	/**
	 * Environment
	 *
	 * @var MT_Environment
	 */
	private $environment;

	/**
	 * Construct
	 *
	 * MT_Field_Declaration_Collection_Builder constructor.
	 *
	 * @param MT_Environment $environment Environment.
	 */
	function __construct( $environment ) {
		$this->field_declarations = array();
		$this->environment = $environment;
	}

	/**
	 * Add Field.
	 *
	 * @param MT_Field_Declaration_Builder $field F.
	 * @return MT_Field_Declaration_Collection_Builder $this
	 */
	function add( $field ) {
		$this->field_declarations[] = $field;
		return $this;
	}

	/**
	 * Build
	 *
	 * @return array
	 */
	function build() {
		return $this->field_declarations;
	}

	/**
	 * New Field Builder
	 *
	 * @param null|string $name Name.
	 * @param null|string $description Description.
	 * @param string      $data_store_type Data store Type.
	 * @return MT_Field_Declaration_Builder
	 */
	function field( $name = null, $description = null, $data_store_type = 'field' ) {
		$builder = new MT_Field_Declaration_Builder();
		if ( ! empty( $name ) ) {
			$builder->with_name( $name );
		}
		if ( ! empty( $description ) ) {
			$builder->with_description( $description );
		}
		if ( 'field' !== $data_store_type ) {
			$builder->with_kind( $data_store_type );
		}
		return $builder;
	}

	/**
	 * Type
	 *
	 * @param string $type_name Type name.
	 * @return MT_Interfaces_Type
	 *
	 * @throws MT_Exception If Invalid type.
	 */
	function type( $type_name ) {
		return $this->environment->get_type_registry()->definition( $type_name );
	}
}
