<?php

interface Mixtape_Interfaces_Model_Field_Type {
    function get_type();

    function sanitize( $value );
}