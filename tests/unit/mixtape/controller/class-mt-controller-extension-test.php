<?php

class TestExtensionModel extends MT_Model_Declaration {
	/**
	 * Field name.
	 *
	 * @var string
	 */
	private $field_name = '_custom_field_on_posts';

	/**
	 * Declare Fields
	 *
	 * @param MT_Environment $env Env.
	 * @return array
	 */
	function declare_fields( $env ) {
		return array(
			$env->field( 'id', 'the post id' )
				->with_map_from( 'ID' ),
			$env->field( $this->field_name, 'A Custom Field on Posts' )
				->with_type( $env->type( 'uint' ) )
				->derived()
				->with_reader( array( $this, 'reader' ) )
				->with_updater( array( $this, 'updater' ) ),
		);
	}

	function reader( $object, $field_name, $request, $object_type ) {
		if ( $field_name !== $this->field_name ) {
			return null;
		}

		$data = get_post_meta( $object['id'], $this->field_name, true );
		return ( false === $data ) ? 0 : absint( $data );
	}

	function updater( $val ) {
		global $post;
		return update_post_meta( $post->ID, '_custom_field_on_posts', $val );
	}
}

class MT_Controller_ExtensionTest extends MT_Testing_Controller_TestCase {

	function test_exists() {
		$this->assertClassExists( 'MT_Controller_Extension' );
	}

	function test_adds_custom_field_getter() {
		// Create post object
		$my_post = array(
			'post_title'    => 'A Post with a custom thing',
			'post_content'  => 'A Post with a custom thing',
			'post_status'   => 'publish',
			'post_author'   => $this->admin_id,
			'meta_input'    => array(
				'_custom_field_on_posts' => '100',
			),
		);

		$post_id = wp_insert_post( $my_post );
		$this->assertNotWPError( $post_id );

		$def_name = 'TestExtensionModel';
		$registrable = new MT_Controller_Extension( 'post', $def_name );
		$this->environment
			->define_model( $def_name );
		$this->environment->add_registrable( $registrable );
		$this->environment->auto_start();
		do_action( 'rest_api_init' );

		$response = $this->get( '/wp/v2/posts/' . $post_id );
		$this->assertNotNull( $response );
		$this->assertResponseStatus( $response, 200 );
		$response_data = $response->get_data();
		$this->assertArrayHasKey( '_custom_field_on_posts', $response_data );
		$this->assertSame( 100, $response_data['_custom_field_on_posts'] );
	}

}