<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Data_Store_Nil
 * Null object for datastores
 */
class MT_Data_Store_Nil implements MT_Interfaces_Data_Store {

	public function get_entities( $filter = null ) {
		return new MT_Model_Collection( array() );
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
