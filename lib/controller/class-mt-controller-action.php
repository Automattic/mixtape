<?php
/**
 * An Acton that is part of a Route.
 *
 * @package MT/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller_Action
 */
class MT_Controller_Action {
	/**
	 * Permitted actions
	 *
	 * @var array
	 */
	private $actions_to_http_methods = array(
		'index' => WP_REST_Server::READABLE,
		'show' => WP_REST_Server::READABLE,
		'create'  => WP_REST_Server::CREATABLE,
		'update' => WP_REST_Server::EDITABLE,
		'delete' => WP_REST_Server::DELETABLE,
		'any' => WP_REST_Server::ALLMETHODS,
	);

	/**
	 * The action name
	 *
	 * @var string
	 */
	private $action_name;

	/**
	 * The Handler
	 *
	 * @var null|array|string
	 */
	private $handler;

	/**
	 * The Permissions Callback
	 *
	 * @var null|array|string
	 */
	private $permission_callback;

	/**
	 * The Args
	 *
	 * @var null|array|string
	 */
	private $args;

	/**
	 * MT_Controller_Action constructor.
	 *
	 * @param MT_Controller $controller Controller.
	 * @param string        $action_name The action Name.
	 */
	public function __construct( $controller, $action_name ) {
		$is_known_action = in_array( $action_name, array_keys( $this->actions_to_http_methods ), true );
		MT_Expect::that( $is_known_action, 'Unknown method: ' . $action_name );

		$this->controller = $controller;
		$this->action_name = $action_name;
		$this->handler = null;
		$this->args = null;
		$this->permission_callback = null;
	}

	/**
	 * Get Name
	 *
	 * @return string
	 */
	public function name() {
		return $this->action_name;
	}

	/**
	 * Set Permissions
	 *
	 * @param mixed $callable A Callable.
	 *
	 * @return MT_Controller_Action
	 */
	public function with_permission_callback( $callable ) {
		$this->permission_callback = $callable;
		return $this;
	}

	/**
	 * Set Handler
	 *
	 * @param mixed $callable A Callable.
	 *
	 * @return MT_Controller_Action
	 */
	public function with_handler( $callable ) {
		$this->handler = $callable;
		return $this;
	}

	/**
	 * Set Handler
	 *
	 * @param mixed $callable A Callable.
	 *
	 * @return MT_Controller_Action
	 */
	public function with_args_callback( $callable ) {
		$this->args = $callable;
		return $this;
	}

	/**
	 * Used in register rest route
	 *
	 * @return array
	 */
	public function as_array() {
		$callable_func = $this->expect_callable( $this->handler );
		if ( null !== $this->permission_callback ) {
			$permission_callback = $this->expect_callable( $this->permission_callback );
		} else {
			$permission_callback = $this->expect_callable( array( $this->controller, $this->action_name . '_permissions_check' ) );
		}

		if ( null !== $this->args ) {
			$args = call_user_func( $this->expect_callable( $this->args ), $this->actions_to_http_methods[ $this->action_name ] );
		} else {
			$args = $this->controller->get_endpoint_args_for_item_schema( $this->actions_to_http_methods[ $this->action_name ] );
		}

		$result = array(
			'methods'             => $this->actions_to_http_methods[ $this->action_name ],
			'callback'            => $callable_func,
			'permission_callback' => $permission_callback,
			'args'                => $args,
		);

		return $result;
	}

	/**
	 * Expect a callable
	 *
	 * @param mixed $callable_func A Callable.
	 * @return array
	 * @throws MT_Exception If not a callable.
	 */
	private function expect_callable( $callable_func ) {
		if ( ! is_callable( $callable_func ) ) {
			// Check if controller has a public method called $callable_func.
			if ( is_string( $callable_func ) && method_exists( $this->controller, $callable_func ) ) {
				return array( $this->controller, $callable_func );
			}
			MT_Expect::that( is_callable( $callable_func ), 'Callable Expected: $callable_func' );
		}
		return $callable_func;
	}
}
