<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mixtape_Type_Boolean extends Mixtape_Type {

	public function __construct() {
		parent::__construct( 'boolean' );
	}

	public function default_value() {
		return false;
	}

	public function cast( $value ) {
		if ( 'false' === $value ) {
			return false;
		}
		return (bool) $value;
	}
}
