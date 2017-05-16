<?php

class Mixtape_Testing_Model_TestCase extends Mixtape_Testing_TestCase {
    /**
     * @var Mixtape_Environment
     */
    protected $environment;
    /**
     * @var Mixtape_Bootstrap
     */
    protected $mixtape;

    function setUp() {
        parent::setUp();
        $this->mixtape = Mixtape_Bootstrap::create()->load();
        $this->environment = $this->mixtape->environment();
    }
}