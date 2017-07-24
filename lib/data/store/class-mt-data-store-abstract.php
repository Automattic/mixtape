<?php
/**
 * Data Store Abstract
 *
 * @package MT/Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Data_Store_Abstract
 * An abstract Data_Store class that contains a model factory
 */
abstract class MT_Data_Store_Abstract implements MT_Interfaces_Data_Store {

	/**
	 * Definition
	 *
	 * @var MT_Model_Factory
	 */
	protected $model_factory;

	/**
	 * Type Serializers
	 *
	 * @var array
	 */
	private $type_serializers;

	/**
	 * MT_Data_Store_Abstract constructor.
	 *
	 * @param null|MT_Model_Factory $model_factory Def.
	 * @param array                 $args Args.
	 */
	public function __construct( $model_factory = null, $args = array() ) {
		$this->type_serializers = array();
		$this->args = $args;
		if ( is_a( $model_factory, 'MT_Model_Factory' ) ) {
			$this->set_model_factory( $model_factory );
		}
	}

	/**
	 * Set Definition
	 *
	 * @param MT_Model_Factory $factory Def.
	 *
	 * @return MT_Interfaces_Data_Store $this
	 */
	private function set_model_factory( $factory ) {
		$this->model_factory = $factory;
		$this->configure();
		return $this;
	}

	/**
	 * Configure
	 */
	protected function configure() {
	}

	/**
	 * Get Definition
	 *
	 * @return MT_Model_Factory
	 */
	public function get_model_factory() {
		return $this->model_factory;
	}
}
