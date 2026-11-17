<?php
/**
 * Minimal stand-ins for the framework and WordPress/ClassicPress pieces that
 * the package uses, so the tests run without an install.
 */

namespace Backdrop\Contracts {
    interface Bootable { public function boot(): void; }
}

namespace Backdrop\Proxies {
    class App {
        public static $services = [];
        public static function resolve( string $abstract ) { return static::$services[ $abstract ]; }
    }
}

namespace {
    $GLOBALS['hooks'] = [];

    class WP_Customize_Control {
        public $id = '';
        public $choices = [];
        public $manager;
        protected function render_content() {}
    }

    class WP_Customize_Manager {
        public $panels = [];
        public $sections = [];
        public $control_types = [];
        public $controls = [];
        public $settings = [];

        public function __construct() {
            foreach ( [ 'custom_css', 'title_tagline', 'static_front_page', 'colors', 'header_image' ] as $id ) {
                $this->sections[ $id ] = (object) [ 'panel' => '', 'title' => $id, 'priority' => 160 ];
            }
        }
        public function add_panel( $id, $args ) { $this->panels[ $id ] = $args; }
        public function get_section( $id ) { return $this->sections[ $id ] ?? null; }
        public function remove_section( $id ) { unset( $this->sections[ $id ] ); }
        public function register_control_type( $class ) { $this->control_types[] = $class; }
        public function get_control( $id ) { return $this->controls[ $id ] ?? null; }
    }

    function esc_html__( $s ) { return $s; }
    function absint( $n ) { return abs( (int) $n ); }
    function wp_validate_boolean( $v ) { return is_string( $v ) && 'false' === strtolower( $v ) ? false : (bool) $v; }
    function get_post_status( $id ) { return [ 5 => 'publish', 6 => 'private' ][ $id ] ?? false; }
    function add_action( $hook, $cb, $priority = 10 ) { $GLOBALS['hooks'][ $hook ][ $priority ][] = $cb; }
    function do_action( $hook, ...$args ) {
        $callbacks = $GLOBALS['hooks'][ $hook ] ?? [];
        ksort( $callbacks );
        foreach ( $callbacks as $list ) { foreach ( $list as $cb ) { $cb( ...$args ); } }
    }
    function add_filter( $hook, $cb, $priority = 10 ) { add_action( $hook, $cb, $priority ); }
    function apply_filters( $hook, $value, ...$args ) {
        foreach ( $GLOBALS['hooks'][ $hook ] ?? [] as $list ) { foreach ( $list as $cb ) { $value = $cb( $value, ...$args ); } }
        return $value;
    }

    spl_autoload_register( function ( $class ) {
        $prefix = 'Backdrop\\Customize\\';
        if ( 0 === strpos( $class, $prefix ) ) {
            $file = __DIR__ . '/../src/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
            if ( is_file( $file ) ) {
                require $file;
            }
        }
    } );
}
