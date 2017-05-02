<?php

/**
 * Sensei Unit Tests Bootstrap
 *
 * @since 1.9
 */
class Mixtape_Unit_Tests_Bootstrap {
    /** @var \Mixtape_Unit_Tests_Bootstrap instance */
    protected static $instance = null;
    /** @var string directory where wordpress-tests-lib is installed */
    public $wp_tests_dir;
    /** @var string testing directory */
    public $tests_dir;

    public $mixtape_dir;

    public function __construct() {
        ini_set( 'display_errors','on' );
        error_reporting( E_ALL );
        $this->tests_dir    = dirname( __FILE__ );
        $this->mixtape_dir   = dirname( $this->tests_dir );
        $this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';
        // load test function so tests_add_filter() is available
        require_once( $this->wp_tests_dir . '/includes/functions.php' );
        // load Mixtape
        tests_add_filter( 'muplugins_loaded', array( $this, 'load_mixtape' ) );
        // install Mixtape
        tests_add_filter( 'setup_theme', array( $this, 'install_mixtape' ) );
        // load the WP testing environment
        require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );

        $this->includes();
    }

    public function load_mixtape() {
        require_once( $this->mixtape_dir . '/loader.php' );
    }

    public function install_mixtape() {
        // new capabilities after install, in the past we reinited this, but in wp > 4.7 its deprecated.
        // see https://core.trac.wordpress.org/ticket/28374
        $GLOBALS['wp_roles'] = new WP_Roles();
    }

    public function includes() {
        require_once( $this->tests_dir . '/unit-testing-classes/MixtapeTestCase.php' );
    }

    /**
     * Get the single class instance.
     * @return Mixtape_Unit_Tests_Bootstrap
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
Mixtape_Unit_Tests_Bootstrap::instance();

