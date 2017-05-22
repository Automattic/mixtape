<?php
/**
 * Data Stores
 *
 * Provides a unified way for fetching and storing Models
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // End if().

/**
 * Interface MT_Interfaces_Data_Store
 */
interface MT_Interfaces_Data_Store {

	/**
	 * Get all the models (taking into account any filtering)
	 *
	 * @param MT_Interfaces_Model|null $filter A filter.
	 * @return MT_Model_Collection
	 */
	public function get_entities( $filter = null );


	/**
	 * Get a Model Using it's unique identifier
	 *
	 * @param int $id The id of the entity.
	 * @return MT_Interfaces_Model
	 */
	public function get_entity( $id );

	/**
	 * Delete a Model
	 *
	 * @param MT_Interfaces_Model $model The model to delete.
	 * @param array                    $args Args.
	 * @return mixed
	 */
	public function delete( $model, $args = array() );

	/**
	 * Update/Insert Model
	 *
	 * @param MT_Interfaces_Model $model The model.
	 * @return mixed
	 */
	public function upsert( $model );
}
