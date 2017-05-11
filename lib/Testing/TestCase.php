
<?php

class Mixtape_Testing_TestCase extends WP_UnitTestCase {
    protected function assertClassExists( $className ) {
        return $this->assertTrue( class_exists( $className ), 'Failed Asserting that class ' . $className . ' exists.' );
    }
}