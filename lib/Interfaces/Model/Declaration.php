<?php

interface Mixtape_Interfaces_Model_Declaration {
    /**
     * @param Mixtape_Model_Definition $definition
     * @return array
     */
    public function declare_fields( $definition );

    public function call($method, $args = array());
}