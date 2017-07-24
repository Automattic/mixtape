<?php

class MT_ModelTest_Extend extends MT_Model {

	/**
	 * Declare the fields of our Model.
	 *
	 * @return array list of MT_Field_Declaration
	 */
	public static function declare_fields() {
		$env = self::get_environment();
		return array(
			$env->field( 'foo' )
				->with_type( $env->type( 'int' ) ),
		);
	}

	/**
	 * Get this model's unique identifier
	 *
	 * @return mixed a unique identifier
	 */
	function get_id() {
		return 1;
	}
}

class MT_ModelTest extends MT_Testing_Model_TestCase {
    function setUp() {
        parent::setUp();
    }

    function test_exists() {
        $this->assertClassExists( 'MT_Model' );
    }

    function test_get() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( 1, $tape->get( 'id' ) );
        $this->assertEquals( 'Awesome Mix Vol 1', $tape->get( 'title' ) );
    }

    function test_get_with_meta_fields() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( array( 1, 2, 3 ), $tape->get( 'songs' ) );
    }

    function test_with_derived_fields() {
        $tape = $this->create_awesome_mix( 1 );
        $this->assertEquals( array( 1 ), $tape->get( 'ratings' ) );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_set_throws_if_field_unknown() {
        $tape = $this->create_awesome_mix( 1 );
        $tape->set( 'foobar', 1 );
    }

    /**
     * @expectedException MT_Exception
     */
    function test_get_throws_if_field_unknown() {
        $tape = $this->create_awesome_mix( 1 );
        $tape->get( 'foobar' );
    }

    function create_awesome_mix( $vol ) {
        return $this->create_casette( array(
            'id' => $vol,
            'title' => 'Awesome Mix Vol ' . $vol,
            'songs' => array( 1, 2, 3 )
        ) );
    }

    function create_casette( $props ) {
		$this->mixtape->environment()->define_model( 'Casette' );
		return $this->mixtape->environment()->model( 'Casette' )->create( $props );
    }

	function test_add_model_definition() {
		$env = $this->mixtape->load()
			->environment();
		$env->define_model( 'Casette' );
		$model_definition = $env->model( 'Casette' );
		$this->assertInstanceOf( 'MT_Model_Factory', $model_definition );
		$this->assertEquals( get_class( $model_definition ), 'MT_Model_Factory' );
		$this->assertInstanceOf( 'MT_Data_Store_Nil', $model_definition->get_data_store() );
	}

	function test_get_field_declarations() {
		$this->mixtape
			->environment()
			->define_model( 'Casette' );
		$model_definition= $this->mixtape->environment()
			->model( 'Casette' );
		$declarations = $model_definition->get_fields();
		$this->assertInternalType( 'array', $declarations );
		$this->assertEquals( 6, count( $declarations ) );
	}

	function test_create_with_array() {
		$this->mixtape
			->environment()
			->define_model( 'Casette' );
		$casette = $this->mixtape
			->environment()->model( Casette::class )
			->create( array(
				'title' => 'Awesome',
				'songs' => array(1,2,3)
			) );

		$this->assertNotNull( $casette );
		$this->assertInstanceOf( MT_Model::class, $casette );
	}

	function test_find_one_by_id_from_cpt_entity_return_null_when_id_not_in_db() {
		$env = $this->mixtape
			->environment();
		$casette_definition = $this->get_casette_definition();

		$model = $casette_definition->get_data_store()->get_entity( -1 );
		$this->assertNull( $model );
	}

	function test_find_one_by_id_from_cpt_entity_return_model() {
		$env = $this->mixtape
			->environment();
		$casette_definition = $this->get_casette_definition();

		$casette_to_insert = $casette_definition->create( array(
			'title' => 'Awesomeness',
			'author' => get_current_user_id(),
			'status' => 'publish',
			'songs'  => array( 1, 2, 3 )
		) );

		$id = $casette_definition->get_data_store()->upsert( $casette_to_insert );
		if ( is_wp_error( $id ) ) {
			var_dump($id);
		}
		$this->assertFalse( is_wp_error( $id ) );
		$model = $casette_definition->get_data_store()->get_entity( $id );
		$this->assertNotNull( $model );
		$this->assertInstanceOf( MT_Model::class, $model );
		$model_id = $model->get( 'id' );
		$this->assertEquals( $id, $model_id );
		$model_meta_field = $model->get( 'songs' );
		$this->assertEquals( array( 1,2,3 ), $model_meta_field );
	}

	private function get_casette_definition() {
		$env = $this->mixtape
			->environment();
		$env->define_model( 'Casette' )
			->with_data_store( new MT_Data_Store_CustomPostType( $env->model( 'Casette' ), array( 'post_type' => 'mixtape_cassette' ) ) );
		return $env->model( 'Casette' );
	}
}