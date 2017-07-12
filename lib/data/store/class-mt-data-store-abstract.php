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
 * An abstract Data_Store class that contains a model definition
 * It also contains helpers for data mapping and serialization
 */
abstract class MT_Data_Store_Abstract implements MT_Interfaces_Data_Store {
	/**
	 * Declaration
	 *
	 * @var MT_Interfaces_Model_Declaration
	 */
	protected $model_declaration;

	/**
	 * Definition
	 *
	 * @var MT_Model_Definition
	 */
	protected $definition;

	/**
	 * Serializer
	 *
	 * @var MT_Data_Serializer
	 */
	protected $serializer;

	/**
	 * Mapper
	 *
	 * @var MT_Data_Mapper
	 */
	protected $data_mapper;

	/**
	 * Type Serializers
	 *
	 * @var array
	 */
	private $type_serializers;

	/**
	 * MT_Data_Store_Abstract constructor.
	 *
	 * @param null|MT_Model_Definition $definition Def.
	 * @param array                    $args Args.
	 */
	public function __construct( $definition = null, $args = array() ) {
		$this->type_serializers = array();
		$this->args = $args;
		if ( is_a( $definition, 'MT_Model_Definition' ) ) {
			$this->set_definition( $definition );
		}
	}

	/**
	 * Set Definition
	 *
	 * @param MT_Model_Definition $definition Def.
	 *
	 * @return MT_Interfaces_Data_Store $this
	 */
	private function set_definition( $definition ) {
		$this->definition        = $definition;
		$this->model_declaration = $this->definition->get_model_declaration();
		$this->serializer        = new MT_Data_Serializer( $this->definition );
		$this->data_mapper       = new MT_Data_Mapper( $this->definition, $this->serializer );
		$this->configure();
		return $this;
	}

	/**
	 * Configure
	 */
	protected function configure() {
	}

	/**
	 * Get Model Declaration
	 *
	 * @return MT_Interfaces_Model_Declaration
	 */
	public function get_model_declaration() {
		return $this->model_declaration;
	}

	/**
	 * Get Definition
	 *
	 * @return MT_Model_Definition
	 */
	public function get_definition() {
		return $this->definition;
	}

	/**
	 * Get Serializer
	 *
	 * @return MT_Data_Serializer
	 */
	public function get_serializer() {
		return $this->serializer;
	}

	/**
	 * Data Mapper
	 *
	 * @return MT_Data_Mapper
	 */
	public function get_data_mapper() {
		return $this->data_mapper;
	}
}
