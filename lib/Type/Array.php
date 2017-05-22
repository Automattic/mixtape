<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mixtape_Type_Array extends Mixtape_Type {

	public function __construct() {
		parent::__construct( 'array' );
	}

	public function default_value() {
		return array();
	}

	public function cast( $value ) {
		return (array) $value;
	}
}
