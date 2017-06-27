<?php

class MT_Model_CollectionTest extends MT_Testing_Model_TestCase {
	function test_exists() {
		$this->assertClassExists( 'MT_Model_Collection' );
	}

	function test_get_items_return_iterator() {
		$arr = array();
		$collection = new MT_Model_Collection( $arr );
		$this->assertInstanceOf( 'Iterator', $collection->get_items() );
	}
}