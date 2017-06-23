<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MT_Field_Declaration_Collection_Builder {
	/**
	 * @var array
	 */
	private $field_declarations;
	/**
	 * @var MT_Environment
	 */
	private $environment;

	function __construct( $environment ) {
		$this->field_declarations = array();
		$this->environment = $environment;
	}

	/**
	 * @param MT_Field_Declaration_Builder $field
	 * @return MT_Field_Declaration_Collection_Builder $this
	 */
	function add( $field ) {
		$this->field_declarations[] = $field;
		return $this;
	}

	function build() {
		return $this->field_declarations;
	}

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
	 * @param $type_name
	 * @return MT_Interfaces_Type
	 * @throws MT_Exception
	 */
	function type( $type_name ) {
		return $this->environment->type()->definition( $type_name );
	}
}
