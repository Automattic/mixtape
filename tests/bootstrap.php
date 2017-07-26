<?php

class Mixtape_Unit_Tests_Bootstrap {
    /** @var \Mixtape_Unit_Tests_Bootstrap instance */
    protected static $instance = null;
    /** @var string directory where wordpress-tests-lib is installed */
    public $wp_tests_dir;
    /** @var string testing directory */
    public $tests_dir;

    public $mixtape_dir;
    private $mixtape_example_dir;

    public function __construct() {
        ini_set( 'display_errors','on' );
        error_reporting( E_ALL );
        $this->tests_dir    = dirname( __FILE__ );
        $this->mixtape_dir   = dirname( $this->tests_dir );
        $this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';
        $this->mixtape_example_dir = $this->mixtape_dir . DIRECTORY_SEPARATOR .  'mixtape-example';
        // load test function so tests_add_filter() is available
        require_once( $this->wp_tests_dir . '/includes/functions.php' );
        // load Mixtape
        tests_add_filter( 'muplugins_loaded', array( $this, 'load_mixtape' ) );
        // install Mixtape
        tests_add_filter( 'setup_theme', array( $this, 'install_mixtape' ) );
        // register our Test Custom Post Types
        tests_add_filter( 'init', array( $this, 'register_mixtape_test_cpts' ) );
        // load the WP testing environment
        require_once( $this->wp_tests_dir . '/includes/bootstrap.php' );
        $this->includes();
    }

    public function load_mixtape() {
        require_once( $this->mixtape_dir . '/lib/class-mt-bootstrap.php' );
    }

    public function install_mixtape() {
        // new capabilities after install, in the past we reinited this, but in wp > 4.7 its deprecated.
        // see https://core.trac.wordpress.org/ticket/28374
        $GLOBALS['wp_roles'] = new WP_Roles();
    }

    public function register_mixtape_test_cpts() {
        // register some post types for using with our tests
        include_once( path_join( $this->mixtape_example_dir, 'class-casette-post-types.php' ) );
        Casette_Post_Types::register();
    }

    public function includes() {
     	include_once 'includes/class-mt-testing-testcase.php';
    }

    public function include_example_classes() {
        include_once( $this->mixtape_example_dir . DIRECTORY_SEPARATOR . 'Casette.php' );
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
