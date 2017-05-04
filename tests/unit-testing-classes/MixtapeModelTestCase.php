<?php

class MixtapeModelTestCase extends MixtapeTestCase {
    /**
     * @var Mixtape_Environment
     */
    protected $environment;
    /**
     * @var Mixtape
     */
    protected $mixtape;

    function setUp() {
        parent::setUp();
        $this->mixtape = Mixtape::create()->load();
        $this->environment = $this->mixtape->environment();
        if ( !class_exists( 'Casette' ) ) {
            // include our test classes
            include_once 'Casette.php';
        }
    }
}