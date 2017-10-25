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
	/**
	 * Events
	 *
	 * @var array
	 */
	private $events = array();

	/**
	 * Add Filter
	 *
	 * @param string   $event Event.
	 * @param callable $func Func.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Accepted Args.
	 *
	 * @return $this
	 */
	public function add_filter( $event, $func, $priority = 10, $accepted_args = 1 ) {
		MT_Expect::that( is_callable( $func ), sprintf( '%s is not callable', $func ) );
		if ( ! isset( $this->events[ $event ] ) ) {
			$this->events[ $event ] = new WP_Hook();
		}
		$hook = $this->events[ $event ];
		$hook->add_filter( $event, $func, $priority, $accepted_args );
		return $this;
	}

	/**
	 * Add Action
	 *
	 * @param string   $event Event.
	 * @param callable $func Func.
	 * @param int      $priority Priority.
	 * @param int      $accepted_args Accepted Args.
	 *
	 * @return $this
	 */
	public function add_action( $event, $func, $priority = 10, $accepted_args = 1 ) {
		return $this->add_filter( $event, $func, $priority, $accepted_args );
	}

	/**
	 * Apply Filters
	 *
	 * @param string $event Event.
	 * @param mixed  $value Value.
	 *
	 * @return mixed
	 */
	public function apply_filters( $event, $value ) {
		$func_args = func_get_args();
		if ( ! isset( $this->events[ $event ] ) ) {
			return $value;
		}
		$hook = $this->events[ $event ];
		return $hook->apply_filters( $func_args );

	}

	/**
	 * Do Action
	 *
	 * @param string $event Event.
	 *
	 * @return mixed
	 */
	public function do_action( $event ) {
		$func_args = func_get_args();
		if ( ! isset( $this->events[ $event ] ) ) {
			return;
		}
		/**
		 * The hook.
		 *
		 * @var WP_Hook $hook
		 */
		$hook = $this->events[ $event ];
		array_shift( $func_args );
		$hook->do_action( $func_args );
	}

	/**
	 * Remove
	 *
	 * @param string        $event The Event.
	 * @param bool|callable $function_to_remove The callable.
	 * @param int           $priority Priority.
	 * @return $this
	 */
	public function remove( $event, $function_to_remove = false, $priority = 10 ) {
		if ( isset( $this->events[ $event ] ) ) {
			if ( false === $function_to_remove ) {
				/**
				 * The hook.
				 *
				 * @var WP_Hook $hook
				 */
				$hook = $this->events[ $event ];
				$hook->remove_all_filters();
				unset( $this->events[ $event ] );
			}
			/**
			 * The hook.
			 *
			 * @var WP_Hook $hook
			 */
			$hook = $this->events[ $event ];
			if ( $hook->has_filter( $event, $function_to_remove ) ) {
				$hook->remove_filter( $event, $function_to_remove, $priority );
			}
		}
		return $this;
	}
}
