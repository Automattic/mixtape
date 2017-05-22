<?php

if (count($argv) !== 4) {
  echo "Too few arguments" . PHP_EOL;
  die(1);
}

$prefix          = trim( $argv[1] );
$lib_dir         = trim( $argv[2] );
$destination_dir = trim( $argv[3] );

if ( !is_dir($lib_dir) ) {
  echo $lib_dir . " Not a Directory" . PHP_EOL;
  die(1);
}

if ( !is_dir($destination_dir) ) {
  echo $destination_dir . " Not a Directory" . PHP_EOL;
  die(1);
}

$prefix = rtrim( $prefix, '_' ); // ensure no trailing `_`s 

define ('ABSPATH', $lib_dir); // define ABSPATH so we can move on

/**
 * Test if a give filesystem path is absolute.
 *
 * For example, '/foo/bar', or 'c:\windows'.
 *
 * @since 2.5.0
 *
 * @param string $path File path.
 * @return bool True if path is absolute, false is not absolute.
 */
function path_is_absolute( $path ) {
    /*
     * This is definitive if true but fails if $path does not exist or contains
     * a symbolic link.
     */
    if ( realpath($path) == $path )
        return true;

    if ( strlen($path) == 0 || $path[0] == '.' )
        return false;

    // Windows allows absolute paths like this.
    if ( preg_match('#^[a-zA-Z]:\\\\#', $path) )
        return true;

    // A path starting with / or \ is absolute; anything else is relative.
    return ( $path[0] == '/' || $path[0] == '\\' );
}

/**
 * Join two filesystem paths together.
 *
 * For example, 'give me $path relative to $base'. If the $path is absolute,
 * then it the full path is returned.
 *
 * @since 2.5.0
 *
 * @param string $base Base path.
 * @param string $path Path relative to $base.
 * @return string The path with the base or absolute path.
 */
function path_join( $base, $path ) {
    if ( path_is_absolute($path) )
        return $path;

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Test if a given path is a stream URL
 *
 * @param string $path The resource path or URL.
 * @return bool True if the path is a stream URL.
 */
function wp_is_stream( $path ) {
    $wrappers = stream_get_wrappers();
    $wrappers_re = '(' . join('|', $wrappers) . ')';

    return preg_match( "!^$wrappers_re://!", $path ) === 1;
}

/**
 * Recursive directory creation based on full path.
 *
 * Will attempt to set permissions on folders.
 *
 * @since 2.0.1
 *
 * @param string $target Full path to attempt to create.
 * @return bool Whether the path was created. True if path already exists.
 */
function wp_mkdir_p( $target ) {
    $wrapper = null;

    // Strip the protocol.
    if ( wp_is_stream( $target ) ) {
        list( $wrapper, $target ) = explode( '://', $target, 2 );
    }

    // From php.net/mkdir user contributed notes.
    $target = str_replace( '//', '/', $target );

    // Put the wrapper back on the target.
    if ( $wrapper !== null ) {
        $target = $wrapper . '://' . $target;
    }

    /*
     * Safe mode fails with a trailing slash under certain PHP versions.
     * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
     */
    $target = rtrim($target, '/');
    if ( empty($target) )
        $target = '/';

    if ( file_exists( $target ) )
        return @is_dir( $target );

    // We need to find the permissions of the parent folder that exists and inherit that.
    $target_parent = dirname( $target );
    while ( '.' != $target_parent && ! is_dir( $target_parent ) ) {
        $target_parent = dirname( $target_parent );
    }

    // Get the permission bits.
    if ( $stat = @stat( $target_parent ) ) {
        $dir_perms = $stat['mode'] & 0007777;
    } else {
        $dir_perms = 0777;
    }

    if ( @mkdir( $target, $dir_perms, true ) ) {

        /*
         * If a umask is set that modifies $dir_perms, we'll have to re-set
         * the $dir_perms correctly with chmod()
         */
        if ( $dir_perms != ( $dir_perms & ~umask() ) ) {
            $folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
            for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
                @chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
            }
        }

        return true;
    }

    return false;
}

if ( ! class_exists('MT_Bootstrap') ) {
    include_once($lib_dir . DIRECTORY_SEPARATOR . 'lib/class-mt-bootstrap.php');
}

include_once ( $lib_dir . DIRECTORY_SEPARATOR . 'lib/interfaces/class-mt-interfaces-classloader.php' );
include_once ( $lib_dir . DIRECTORY_SEPARATOR . 'lib/class-mt-classloader.php' );

class MT_Classloader_PrefixGenerator implements MT_Interfaces_Classloader {
    /**
     * @var bool should we autoload? Defaults to true
     */
    private $should_autoload;
    /**
     * @var string the class prefix
     */
    private $prefix;
    /**
     * @var string this file's dir
     */
    private $base_dir;
    /**
     * @var string the class library dir
     */
    private $lib_dir;
    /**
     * @var string the default prefix
     */
    private $default_prefix = 'MT';
    private $prefix_dir;
    /**
     * @var MT_Classloader
     */
    private $class_loader;
    private $is_debugging;

    /**
     * Mixtape_Class_Loader constructor.
     * @param array $settings
     * @throws Exception
     */
    public function __construct( $settings ) {
        $this->is_debugging    = isset( $settings['debug'] ) ? (bool)$settings['debug'] : false;
        $this->prefix          = isset( $settings['prefix'] ) ? $settings['prefix'] : $this->default_prefix;
        $this->base_dir        = isset( $settings['base_dir'] ) ? $settings['base_dir'] : dirname( __FILE__ );
        $this->should_autoload = isset( $settings['should_autoload'] ) ? (bool)$settings['should_autoload'] : true;
        $this->lib_dir         = isset( $settings['lib_dir'] ) ? $settings['lib_dir'] : $this->get_default_lib_dir();
        $this->prefix_dir      = isset( $settings['prefix_dir'] ) ? $settings['prefix_dir'] : $this->get_default_prefix_dir();
        if ( ! is_dir( $this->prefix_dir ) ) {
            wp_mkdir_p( $this->prefix_dir );
        }
        $this->class_loader    = new MT_Classloader( $this->prefix, $this->prefix_dir );
    }

    private function get_default_lib_dir() {
        $lib_dir = $this->base_dir . DIRECTORY_SEPARATOR . 'lib';

        return $lib_dir;
    }

    private function get_default_prefix_dir() {
        if ( $this->has_custom_prefix() ) {
            return $this->base_dir . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR . strtolower( $this->prefix );
        } else {
            return $this->lib_dir;
        }
    }
    private function has_custom_prefix() {
        return $this->prefix !== $this->default_prefix;
    }

    public function load_class( $name ) {
        $path = $this->class_loader->get_path_to_class_file( $name );
        $dir_to_create = dirname( $path );
        echo "Creating Dir \n    - $dir_to_create" . PHP_EOL;
        if ( wp_mkdir_p( $dir_to_create ) ) {
            $template_path = $this->get_default_prefix_class_path( $name );
            if ( !is_file( $template_path ) ) {
                throw new Exception( 'Template path does not exist: ' . $template_path );
            }
            $content = str_replace( $this->default_prefix, $this->prefix, file_get_contents( $template_path ) );
            $content = str_replace( 'class-mt-', 'class-' . strtolower( str_replace( '_', '-', $this->prefix ) ) . '-', $content );
            echo "Copying \n    - $template_path to\n    - $path " . PHP_EOL;
            @file_put_contents( $path, $content );
        }

        if ( $this->has_custom_prefix() && !file_exists( $path ) || $this->is_debugging ) {

        }

        return $this;
    }

    public function prefixed_class_name( $class_name ) {
        return $this->class_loader->prefixed_class_name( $class_name );
    }

    private function get_default_prefix_class_path( $name ) {
        return path_join( $this->lib_dir, $this->class_loader->class_name_to_relative_path( $name, $this->default_prefix ) );
    }
}

$class_loader = new MT_Classloader_PrefixGenerator(array(
    'prefix' => $prefix,
    'base_dir' => $lib_dir,
    'prefix_dir' => $destination_dir,
    'is_debugging' => false,
));

$mixtape = MT_Bootstrap::create( $class_loader );

echo 'Generating Prefixed Mixtape';

$mixtape->class_loader()
    ->load_class( 'Interfaces_Classloader' )
    ->load_class( 'Classloader' )
    ->load_class( 'Bootstrap' );
$mixtape->load();


