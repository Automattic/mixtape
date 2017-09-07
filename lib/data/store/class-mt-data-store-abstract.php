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
	 * @var MT_Model
	 */
	protected $model_prototype;

	/**
	 * Type Serializers
	 *
	 * @var array
	 */
	private $type_serializers;

	/**
	 * MT_Data_Store_Abstract constructor.
	 *
	 * @param null|MT_Model $model_prototype Def.
	 * @param array         $args Args.
	 */
	public function __construct( $model_prototype = null, $args = array() ) {
		$this->type_serializers = array();
		$this->args = $args;
		MT_Expect::is_a( $model_prototype, 'MT_Interfaces_Model' );
		$this->set_model_factory( $model_prototype );
	}

	/**
	 * Set Definition
	 *
	 * @param MT_Model $factory Def.
	 *
	 * @return MT_Interfaces_Data_Store $this
	 */
	private function set_model_factory( $factory ) {
		$this->model_prototype = $factory;
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
	 * @return MT_Model
	 */
	public function get_model_prototype() {
		return $this->model_prototype;
	}
}
