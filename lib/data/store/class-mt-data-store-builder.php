<?php
/**
 * Data Store Builder
 *
 * Builder assumes that the datat store class is compatible with Abstract
 *
 * @package MT/Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Data_Store_Builder
 */
class MT_Data_Store_Builder {
	/**
	 * Args.
	 *
	 * @var array
	 */
	private $args = array();
	/**
	 * The Model Class.
	 *
	 * @var string
	 */
	private $store_class = 'MT_Data_Store_CustomPostType';
	/**
	 * @var MT_Model_Definition
	 */
	private $model_definition;

	/**
	 * With class
	 *
	 * @param string $data_store_class Class.
	 * @return MT_Data_Store_Builder $this
	 * @throws MT_Exception If Class invalid.
	 */
	public function with_class( $data_store_class ) {
		$implements_data_store = in_array( 'MT_Interfaces_Data_Store', class_implements( $data_store_class ), true );
		MT_Expect::that( $implements_data_store, $data_store_class . ' should be a ' . $data_store_class );
		$this->store_class = $data_store_class;
		return $this;
	}

	/**
	 * Set Args
	 *
	 * @param array $args Args.
	 * @return MT_Data_Store_Builder $this
	 */
	function with_args( $args ) {
		$this->args = $args;
		return $this;
	}

	/**
	 * Set Model Definition
	 *
	 * @param string|MT_Model_Definition $model_definition Def.
	 * @return MT_Data_Store_Builder $this
	 */
	function with_model_definition( $model_definition ) {
		$this->model_definition = $model_definition;
		return $this;
	}

	/**
	 * Build
	 *
	 * @return MT_Interfaces_Data_Store
	 */
	function build() {
		$store_class = $this->store_class;
		return new $store_class( $this->model_definition, $this->args );
	}
}
