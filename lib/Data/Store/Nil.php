<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Data_Store_Nil
 * Null object for datastores
 */
class Mixtape_Data_Store_Nil implements Mixtape_Interfaces_Data_Store {

	public function get_entities( $filter = null ) {
		return new Mixtape_Model_Collection( array() );
	}

	public function get_entity( $id ) {
		return null;
	}

	public function delete( $model, $args = array() ) {
		return true;
	}

	public function upsert( $model ) {
		return 0;
	}

	public function set_definition( $definition ) {
	}
}
