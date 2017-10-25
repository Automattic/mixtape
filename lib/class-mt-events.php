<?php
/**
 * MT_Events: Simple event bus
 *
 * @package Mixtape
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class MT_Events
 *
 * Simple event bus
 *
 * @package Mixtape
 */
class MT_Events {
    private $events = array();

    public function add_filter( $event, $func, $priority = 10, $accepted_args = 1 ) {
        MT_Expect::that( is_callable( $func ), sprintf('%s is not callable', $func ) );
        if ( ! isset( $this->events[ $event ] ) ) {
            $this->events[ $event ] = new WP_Hook();
        }
        $hook = $this->events[ $event ];
        $hook->add_filter( $event, $func, $priority, $accepted_args );
        return $this;
    }

    public function add_action( $event, $func, $priority = 10, $accepted_args = 1 ) {
        return $this->add_filter( $event, $func, $priority, $accepted_args );
    }

    public function apply_filters( $event, $value ) {
        $func_args = func_get_args();
        if ( ! isset( $this->events[ $event ] ) ) {
            return $value;
        }
        $hook = $this->events[ $event ];
        return $hook->apply_filters( $func_args );

    }

    public function do_action( $event ) {
        $func_args = func_get_args();
        if ( ! isset( $this->events[ $event ] ) ) {
            return;
        }
        /**
         * @var WP_Hook $hook The hook.
         */
        $hook = $this->events[ $event ];
        array_shift( $func_args );
        $hook->do_action( $func_args );

    }

    public function remove( $event, $function_to_remove = false, $priority = 10 ) {
        if ( isset( $this->events[ $event ] ) ) {
            if ( false === $function_to_remove ) {
                /**
                 * @var WP_Hook $hook The hook.
                 */
                $hook = $this->events[ $event ];
                $hook->remove_all_filters();
                unset( $this->events[ $event ] );
            }
            /**
             * @var WP_Hook $hook The hook.
             */
            $hook = $this->events[ $event ];
            if ( $hook->has_filter( $event, $function_to_remove ) ) {
                $hook->remove_filter( $event, $function_to_remove, $priority );
            }
        }
        return $this;
    }
}