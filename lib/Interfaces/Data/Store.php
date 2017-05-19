<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Interface Mixtape_Interfaces_Data_Store
 */
interface Mixtape_Interfaces_Data_Store {

    /**
     * @param Mixtape_Interfaces_Model|null $filter possibly a filter model
     * @return Mixtape_Model_Collection
     */
    public function get_entities( $filter = null );


    /**
     * @param int $id the id of the entity
     * @return Mixtape_Interfaces_Model
     */
    public function get_entity( $id );

    /**
     * @param Mixtape_Interfaces_Model $model
     * @param array $args
     * @return mixed
     */
    public function delete( $model, $args = array() );

    /**
     * @param Mixtape_Interfaces_Model $model
     * @return mixed
     */
    public function upsert( $model );
}