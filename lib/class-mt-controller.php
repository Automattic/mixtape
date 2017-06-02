<?php
/**
 * Controller
 *
 * @package Mixtape/Controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MT_Controller
 */
class MT_Controller extends WP_REST_Controller {
	const HTTP_CREATED     = 201;
	const HTTP_SUCCESS     = 200;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_NOT_FOUND   = 404;

	/**
	 * The bundle this belongs to.
	 *
	 * @var MT_Controller_Bundle
	 */
	protected $controller_bundle;

	/**
	 * The endpoint base (e.g. /users). Override in subclasses.
	 *
	 * @var string
	 */
	protected $base = null;

	/**
	 * Optional, an enviromnent.
	 *
	 * @var null|MT_Environment
	 */
	protected $environment = null;

	/**
	 * MT_Rest_Api_Controller constructor.
	 *
	 * @param MT_Controller_Bundle $controller_bundle The Controller Bundle.
	 * @param null|MT_Environment  $environment The Environment.
	 * @throws MT_Exception If no base is set.
	 */
	public function __construct( $controller_bundle = null, $environment = null ) {
		$this->controller_bundle = $controller_bundle;
		$this->set_environment( $environment );
	}

	/**
	 * Set Controller Bundle
	 *
	 * @param MT_Controller_Bundle $controller_bundle Controller Bundle this belongs to.
	 *
	 * @return MT_Controller $this
	 */
	public function set_controller_bundle( $controller_bundle ) {
		$this->controller_bundle = $controller_bundle;
		return $this;
	}

	/**
	 * Set the Environment for this Controller.
	 *
	 * @param MT_Environment|null $environment The Environment.
	 * @return MT_Controller
	 */
	public function set_environment( $environment ) {
		$this->environment = $environment;
		return $this;
	}

	/**
	 * Registers this Controller.
	 *
	 * @throws MT_Exception Override this.
	 */
	public function register() {
		MT_Expect::that( ! empty( $this->base ), 'Need to put a string with a backslash in $base' );
		throw new MT_Exception( 'override me' );
	}

	/**
	 * Succeed
	 *
	 * @param array $data The dto.
	 *
	 * @return WP_REST_Response
	 */
	protected function succeed( $data ) {
		return $this->respond( $data, self::HTTP_SUCCESS );
	}

	/**
	 * Created
	 *
	 * @param array $data The dto.
	 *
	 * @return WP_REST_Response
	 */
	protected function created( $data ) {
		return $this->respond( $data, self::HTTP_CREATED );
	}

	/**
	 * Bad request
	 *
	 * @param array $data The dto.
	 *
	 * @return WP_REST_Response
	 */
	protected function fail_with( $data ) {
		return $this->respond( $data, self::HTTP_BAD_REQUEST );
	}

	/**
	 * Not Found (404)
	 *
	 * @param string $message The message.
	 *
	 * @return WP_REST_Response
	 */
	protected function not_found( $message ) {
		return $this->respond( array(
			'message' => $message,
		), self::HTTP_NOT_FOUND );
	}

	/**
	 * Respond
	 *
	 * @param array|WP_REST_Response|WP_Error|mixed $data The thing.
	 * @param int                                   $status The Status.
	 *
	 * @return mixed|WP_REST_Response
	 */
	private function respond( $data, $status ) {
		if ( is_a( $data, 'WP_REST_Response' ) ) {
			return $data;
		}

		return new WP_REST_Response( $data, $status );
	}
}
