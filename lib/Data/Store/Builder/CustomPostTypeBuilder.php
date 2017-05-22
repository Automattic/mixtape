<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mixtape_Data_Store_Builder_CustomPostTypeBuilder implements Mixtape_Interfaces_Builder {
	private $post_type = 'post';
	/**
	 * @var Mixtape_Model_Definition
	 */
	private $model_definition;

	function with_type( $post_type ) {
		$this->post_type = $post_type;
		return $this;
	}

	function with_model_definition( $model_definition ) {
		$this->model_definition = $model_definition;
		return $this;
	}

	function build() {
		return new Mixtape_Data_Store_CustomPostType( $this->model_definition, $this->post_type );
	}
}
