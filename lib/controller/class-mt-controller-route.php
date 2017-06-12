<?php

/**
 * Class MT_Controller_Route
 */
class MT_Controller_Route {
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
	 * Our pattern
	 *
	 * @var string
	 */
	private $pattern;
	/**
	 * Our Handlers
	 *
	 * @var array
	 */
	private $handlers;
	/**
	 * Our Controller
	 *
	 * @var MT_Controller
	 */
	private $controller;
	/**
	 * HTTP Methods
	 *
	 * @var array
	 */
	private $http_methods;

	/**
	 * MT_Controller_Route constructor.
	 *
	 * @param MT_Controller $controller A Controller.
	 * @param string        $pattern Pattern.
	 */
	public function __construct( $controller, $pattern ) {
		$this->controller = $controller;
		$this->pattern = $pattern;
		$this->handlers = array();
		$this->http_methods = explode( ', ', WP_REST_Server::ALLMETHODS );
	}

	/**
	 * Attach a handler to an action
	 *
	 * @param string                $action The Action (must be one of the known ones).
	 * @param string|array|callable $callable A Callable that will handle this type of action.
	 * @throws MT_Exception In case of unknown action.
	 * @return $this
	 */
	public function handler( $action, $callable ) {
		return $this->set_handler_item( $action, 'callback', $callable );
	}

	/**
	 * Set Permissions
	 *
	 * @param string $action Action.
	 * @param mixed  $callable A Callable.
	 * @return MT_Controller_Route
	 */
	public function permissions( $action, $callable ) {
		return $this->set_handler_item( $action, 'permission_callback', $callable );
	}

	/**
	 * Set Args
	 *
	 * @param string $action Action.
	 * @param mixed  $callable A Callable.
	 * @return MT_Controller_Route
	 */
	public function args( $action, $callable ) {
		return $this->set_handler_item( $action, 'args', $callable );
	}

	/**
	 * Gets Route info to use in Register rest route.
	 *
	 * @throws MT_Exception If invalid callable.
	 * @return array
	 */
	public function as_array() {
		$result = array();
		$result['pattern'] = $this->pattern;
		$result['actions'] = array();
		foreach ( $this->handlers as $action => $settings ) {
			$callable_func = $this->expect_callable( $settings['callback'] );
			if ( null !== $settings['permission_callback'] ) {
				$permission_callback = $this->expect_callable( $settings['permission_callback'] );
			} else {
				$permission_callback = $this->expect_callable( array( $this->controller, $action . '_permissions_check' ) );
			}

			if ( null !== $settings['args'] ) {
				$args = call_user_func( $this->expect_callable( $settings['args'] ) );
			} else {
				$args = $this->controller->get_endpoint_args_for_item_schema( $this->actions_to_http_methods[ $action ] );
			}

			$result['actions'][] = array(
				'methods'             => $this->actions_to_http_methods[ $action ],
				'callback'            => $callable_func,
				'permission_callback' => $permission_callback,
				'args'                => $args,
			);
		}
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
			// Check if controller has method (public).
			if ( is_string( $callable_func ) && method_exists( $this->controller, $callable_func ) ) {
				return array( $this->controller, $callable_func );
			}
			MT_Expect::that( is_callable( $callable_func ), 'Callable Expected: ' . print_r( $callable_func, true ) );
		}
		return $callable_func;
	}

	/**
	 * Set Handler Item
	 *
	 * @param string $action Action.
	 * @param string $item Handler item to set.
	 * @param mixed  $callable Callable.
	 * @return $this
	 * @throws MT_Exception When invalid action.
	 */
	private function set_handler_item( $action, $item, $callable ) {
		$is_known_action = in_array( $action, array_keys( $this->actions_to_http_methods ), true );
		MT_Expect::that( $is_known_action, 'Unknown method: ' . $action );
		$this->get_handler( $action );
		$this->handlers[ $action ][ $item ] = $callable;
		return $this;
	}

	/**
	 * Get Handler for action
	 *
	 * @param string $action Action.
	 * @return array;
	 */
	private function get_handler( $action ) {
		if ( ! isset( $this->handlers[ $action ] ) ) {
			$this->handlers[ $action ] = array(
				'callback' => null,
				'args' => null,
				'permission_callback' => null,
			);
		}
		return $this->handlers[ $action ];
	}
}