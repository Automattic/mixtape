<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Mixtape_Expect {
    /**
     * @param mixed $thing
     * @param string $class_name
     * @param null|string $caller
     * @throws Mixtape_Exception
     */
    static function is_a( $thing, $class_name ) {
        if ( empty( $caller ) ) {
            $caller = __FUNCTION__;
        }
        self::that( is_a( $thing, $class_name ), 'Expected ' . $class_name . ', got ' . get_class( $thing ) );
    }

    /**
     * @param bool $cond
     * @param string $fail_message
     * @throws Mixtape_Exception
     */
    static function that($cond, $fail_message ) {
        if ( ! $cond ) {
            throw new Mixtape_Exception( $fail_message );
        }
    }
}