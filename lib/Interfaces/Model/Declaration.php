<?php
/**
 * Model Declarations
 *
 * The preferred way to customise the Behaviour of a model is to provide it
 * With a class that Implements this Interface.
 *
 * @package Mixtape
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface Mixtape_Interfaces_Model_Declaration
 */
interface Mixtape_Interfaces_Model_Declaration {

	/**
	 * Set this Declaration's Definition
	 *
	 * @param Mixtape_Model_Definition $def The definition.
	 * @return mixed
	 */
	function set_definition( $def );

	/**
	 * Get this Declaration's Definition
	 *
	 * @return Mixtape_Model_Definition
	 */
	function definition();

	/**
	 * Declare the fields of our Model.
	 *
	 * @param Mixtape_Model_Field_Declaration_Collection_Builder $definition The builder.
	 * @return array list of Mixtape_Model_Field_Declaration
	 */
	function declare_fields( $definition );

	/**
	 * Call a method
	 *
	 * @param string $method The method.
	 * @param array  $args The args.
	 * @return mixed
	 */
	function call( $method, $args = array());

	/**
	 * Get this model's unique identifier
	 *
	 * @param Mixtape_Interfaces_Model $model The model.
	 * @return mixed
	 */
	function get_id( $model );

	/**
	 * Set this model's unique identifier
	 *
	 * @param Mixtape_Interfaces_Model $model The model.
	 * @param mixed                    $id The id.
	 *
	 * @return Mixtape_Interfaces_Model The model.
	 */
	function set_id( $model, $id );

	/**
	 * Get the name
	 *
	 * @return string This declaration's name.
	 */
	function get_name();

}
