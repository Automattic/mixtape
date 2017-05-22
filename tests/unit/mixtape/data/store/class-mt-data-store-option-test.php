<?php

class MT_Data_Store_OptionTest extends MT_Testing_Model_TestCase {
    function test_exists() {
        $this->assertClassExists( 'MT_Data_Store_Option' );
    }
}