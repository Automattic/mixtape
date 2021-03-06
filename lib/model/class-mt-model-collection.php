<?php
/**
 * Model Collection Default implementation
 *
 * @package Mixtape/Model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Mixtape_Model_Collection
 *
 * Represents a collection of Mixtape_Interfaces_Model.
 */
class MT_Model_Collection implements MT_Interfaces_Model_Collection {
	/**
	 * The Models
	 *
	 * @var array the models Mixtape_Interfaces_Model
	 */
	private $models;

	/**
	 * Mixtape_Model_Collection constructor.
	 *
	 * @param array $models The models.
	 */
	public function __construct( $models = array() ) {
		$this->models = $models;
	}

	/**
	 * Get the contents of this collection.
	 *
	 * @return Iterator
	 */
	public function get_items() {
		return new ArrayIterator( $this->models );
	}
}
