<?php
/**
 * Data Store Nil (empty)
 *
 * @package MT/Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Data_Store_Nil
 * Null object for datastores
 */
class MT_Data_Store_Nil implements MT_Interfaces_Data_Store {

	/**
	 * Get Entities
	 *
	 * @param null $filter F.
	 * @return MT_Model_Collection
	 */
	public function get_entities( $filter = null ) {
		return new MT_Model_Collection( array() );
	}

	/**
	 * Get Entity
	 *
	 * @param int $id Id.
	 * @return null
	 */
	public function get_entity( $id ) {
		return null;
	}

	/**
	 * Delete
	 *
	 * @param MT_Interfaces_Model $model Model.
	 * @param array               $args Args.
	 * @return bool
	 */
	public function delete( $model, $args = array() ) {
		return true;
	}

	/**
	 * Upsert
	 *
	 * @param MT_Interfaces_Model $model Model.
	 * @return int
	 */
	public function upsert( $model ) {
		return 0;
	}

	/**
	 * Def
	 *
	 * @param mixed $definition Def.
	 */
	public function set_definition( $definition ) {
	}
}
