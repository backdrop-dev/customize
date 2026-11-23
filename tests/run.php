<?php
/**
 * Dependency free tests. Run with: php tests/run.php
 */

require __DIR__ . '/bootstrap.php';

use Backdrop\Customize\Component;
use Backdrop\Customize\Controls\RadioImage;
use Backdrop\Customize\Helpers\Sanitize;
use Backdrop\Proxies\App;

$failed = 0;

function check( string $label, $actual, $expected ): void {
    global $failed;
    if ( $actual === $expected ) {
        echo "ok   - {$label}\n";
        return;
    }
    $failed++;
    echo "FAIL - {$label}\n       expected: " . var_export( $expected, true ) . "\n       actual:   " . var_export( $actual, true ) . "\n";
}

// A customizable component that records what it is asked to register.
class Fake {
    public $calls = [];
    public function registerPanels( $m ) { $this->calls[] = 'panels'; }
    public function registerSections( $m ) { $this->calls[] = 'sections'; }
    public function registerSettings( $m ) { $this->calls[] = 'settings'; }
    public function registerControls( $m ) { $this->calls[] = 'controls'; }
    public function registerPartials( $m ) { $this->calls[] = 'partials'; }
}

App::$services[ Fake::class ] = new Fake();

$component = new Component( [ Fake::class ] );
$component->boot();

$manager = new WP_Customize_Manager();
do_action( 'customize_register', $manager );

check( 'four theme panels', array_keys( $manager->panels ), [ 'theme_global', 'theme_header', 'theme_content', 'theme_footer' ] );
check( 'panel title', $manager->panels['theme_global']['title'], 'Theme: Global' );
check( 'components are called in order', App::$services[ Fake::class ]->calls, [ 'panels', 'sections', 'settings', 'controls', 'partials' ] );
check( 'custom css moves to global', $manager->sections['custom_css']->panel, 'theme_global' );
check( 'title and tagline move to header as Branding', [ $manager->sections['title_tagline']->panel, $manager->sections['title_tagline']->title ], [ 'theme_header', 'Branding' ] );
check( 'header image moves to header', $manager->sections['header_image']->panel, 'theme_header' );
check( 'front page moves to content first', [ $manager->sections['static_front_page']->panel, $manager->sections['static_front_page']->priority ], [ 'theme_content', 5 ] );
check( 'colors section is removed by default', isset( $manager->sections['colors'] ), false );
check( 'radio image control type is registered', $manager->control_types, [ RadioImage::class ] );

// A site without one of the core sections doesn't fail.
$manager = new WP_Customize_Manager();
unset( $manager->sections['header_image'] );
$ok = true;
try { $component->registerSections( $manager ); } catch ( Throwable $e ) { $ok = false; }
check( 'missing core section is skipped', $ok, true );

// The Colors section can be kept.
add_filter( 'backdrop/customize/remove_colors_section', '__return_false' );
function __return_false() { return false; }
$manager = new WP_Customize_Manager();
$component->registerSections( $manager );
check( 'colors section can be kept with a filter', isset( $manager->sections['colors'] ), true );

// Styles for the radio image control.
ob_start();
$component->printStyles();
check( 'styles are printed', false !== strpos( ob_get_clean(), 'radio-image__radio' ), true );

// Sanitize.
$control          = new WP_Customize_Control();
$control->choices = [ 'sidebar' => [], 'full' => [] ];
$manager->controls['layout'] = $control;
$setting          = (object) [ 'id' => 'layout', 'manager' => $manager, 'default' => 'sidebar' ];

check( 'choice is kept', Sanitize::choices( 'full', $setting ), 'full' );
check( 'unknown choice gets the default', Sanitize::choices( 'nope', $setting ), 'sidebar' );
check( 'layouts() is the same as choices()', Sanitize::layouts( 'nope', $setting ), 'sidebar' );
check( 'checkbox true', Sanitize::checkbox( '1' ), true );
check( 'checkbox false string', Sanitize::checkbox( 'false' ), false );
check( 'published page is allowed', Sanitize::dropdown( '5', (object) [ 'default' => 0 ] ), 5 );
check( 'private page falls back to the default', Sanitize::dropdown( '6', (object) [ 'default' => 0 ] ), 0 );

// Built-in components.
use Backdrop\Customize\Controls\RadioImage as RadioImageControl;

function register( Component $component ): WP_Customize_Manager {
    $manager = new WP_Customize_Manager();
    foreach ( [ 'registerPanels', 'registerSections', 'registerSettings', 'registerControls', 'registerPartials' ] as $method ) {
        $component->$method( $manager );
    }
    return $manager;
}

function reset_filters(): void {
    foreach ( array_keys( $GLOBALS['hooks'] ) as $hook ) {
        if ( 0 === strpos( $hook, 'backdrop/customize/' ) && 'backdrop/customize/remove_colors_section' !== $hook ) {
            unset( $GLOBALS['hooks'][ $hook ] );
        }
    }
}

$layouts = [
    'left'  => [ 'label' => 'Left',  'url' => '%1$s/left.png' ],
    'right' => [ 'label' => 'Right', 'url' => '%1$s/right.png' ],
];

// With no filters: Footer credit and Featured image size are registered, Layout waits for choices.
reset_filters();
$manager = register( new Component() );
check( 'layout waits for choices', isset( $manager->sections['theme_content_layout'] ), false );
check( 'footer credit section', [ $manager->sections['theme_footer_credit']->panel, $manager->sections['theme_footer_credit']->title ], [ 'theme_footer', 'Credit' ] );
check( 'powered by is off by default', $manager->settings['theme_footer_powered_by']['default'], false );
check( 'footer credit reloads the preview without a callback', $manager->settings['theme_footer_custom_credit']['transport'], 'refresh' );
check( 'footer credit has no partial without a callback', $manager->selective_refresh->partials, [] );
check( 'footer credit controls', [ $manager->controls['theme_footer_powered_by']->type, $manager->controls['theme_footer_custom_credit']->type ], [ 'checkbox', 'textarea' ] );
check( 'featured image section', $manager->sections['theme_content_feature_image']->panel, 'theme_content' );
check( 'featured image offers the registered sizes and full', array_keys( $manager->controls['theme_content_feature_image']->choices ), [ 'thumbnail', 'medium', 'post-thumbnail', 'full' ] );
check( 'featured image default', $manager->settings['theme_content_feature_image']['default'], 'post-thumbnail' );

// Layout in the content panel (Momentum) and in the global panel (Beyond Expectations).
add_filter( 'backdrop/customize/layout_args', function ( $args ) use ( $layouts ) {
    $args['choices'] = $layouts;
    $args['default'] = 'right';
    return $args;
} );
$manager = register( new Component() );
check( 'layout goes in the content panel by default', [ $manager->sections['theme_content_layout']->panel, $manager->sections['theme_content_layout']->priority ], [ 'theme_content', 10 ] );
check( 'layout default', $manager->settings['theme_content_layout']['default'], 'right' );
check( 'layout reloads the preview by default', $manager->settings['theme_content_layout']['transport'], 'refresh' );
check( 'layout uses the radio image control', $manager->controls['theme_content_layout'] instanceof RadioImageControl, true );

reset_filters();
add_filter( 'backdrop/customize/layout_args', function ( $args ) use ( $layouts ) {
    $args['panel']     = 'theme_global';
    $args['choices']   = $layouts;
    $args['default']   = 'nope';
    $args['transport'] = 'postMessage';
    return $args;
} );
$manager = register( new Component() );
check( 'layout can go in the global panel', [ $manager->sections['theme_global_layout']->panel, isset( $manager->sections['theme_content_layout'] ) ], [ 'theme_global', false ] );
check( 'layout default falls back to the first choice', $manager->settings['theme_global_layout']['default'], 'left' );
check( 'layout transport can be changed', $manager->settings['theme_global_layout']['transport'], 'postMessage' );

$setting = (object) [ 'id' => 'theme_global_layout', 'manager' => $manager, 'default' => 'left' ];
check( 'layout setting keeps a choice', call_user_func( $manager->settings['theme_global_layout']['sanitize_callback'], 'right', $setting ), 'right' );
check( 'layout setting rejects anything else', call_user_func( $manager->settings['theme_global_layout']['sanitize_callback'], 'nope', $setting ), 'left' );

// Footer credit with a render callback and no generated credit.
reset_filters();
add_filter( 'backdrop/customize/footer_credit_args', function ( $args ) {
    $args['powered_by']      = false;
    $args['credit_default']  = 'Copyright';
    $args['render_callback'] = '__return_false';
    return $args;
} );
$manager = register( new Component() );
check( 'footer credit without the generated credit', [ isset( $manager->settings['theme_footer_powered_by'] ), isset( $manager->controls['theme_footer_powered_by'] ) ], [ false, false ] );
check( 'footer credit default', $manager->settings['theme_footer_custom_credit']['default'], 'Copyright' );
check( 'footer credit updates the preview with a callback', $manager->settings['theme_footer_custom_credit']['transport'], 'postMessage' );
check( 'footer credit partial', [ $manager->selective_refresh->partials['theme_footer_credit']['selector'], $manager->selective_refresh->partials['theme_footer_credit']['settings'] ], [ '.app-footer__credit', [ 'theme_footer_custom_credit' ] ] );
check( 'footer credit keeps allowed tags only', call_user_func( $manager->settings['theme_footer_custom_credit']['sanitize_callback'], '<b>Hi</b><script>x</script>' ), '<b>Hi</b>x' );

// Featured image with the theme's own sizes.
reset_filters();
add_filter( 'backdrop/customize/featured_image_args', function ( $args ) {
    $args['choices'] = [ 'small' => 'Small', 'large' => 'Large' ];
    $args['default'] = 'large';
    return $args;
} );
$manager = register( new Component() );
check( 'featured image uses the theme sizes', array_keys( $manager->controls['theme_content_feature_image']->choices ), [ 'small', 'large' ] );
check( 'featured image default from the theme', $manager->settings['theme_content_feature_image']['default'], 'large' );

// Each built-in component can be turned off.
reset_filters();
add_filter( 'backdrop/customize/layout_args', function ( $args ) use ( $layouts ) {
    $args['choices'] = $layouts;
    return $args;
} );
add_filter( 'backdrop/customize/enable_layout', '__return_false' );
add_filter( 'backdrop/customize/enable_footer_credit', '__return_false' );
add_filter( 'backdrop/customize/enable_featured_image', '__return_false' );
$manager = register( new Component() );
check( 'built-in components can be turned off', [ isset( $manager->sections['theme_content_layout'] ), isset( $manager->sections['theme_footer_credit'] ), isset( $manager->sections['theme_content_feature_image'] ) ], [ false, false, false ] );

reset_filters();
add_filter( 'backdrop/customize/enable_footer_credit', '__return_false' );
$manager = register( new Component() );
check( 'turning one off leaves the others', [ isset( $manager->sections['theme_footer_credit'] ), isset( $manager->sections['theme_content_feature_image'] ) ], [ false, true ] );

// A theme's own component registers after the built-in ones, so it replaces them.
reset_filters();
class ThemeFooter extends Fake {
    public function registerSections( $m ) { $m->add_section( 'theme_footer_credit', [ 'panel' => 'theme_footer', 'title' => 'Mine' ] ); }
}
App::$services[ ThemeFooter::class ] = new ThemeFooter();
$manager = register( new Component( [ ThemeFooter::class ] ) );
check( 'a theme section replaces the built-in one', $manager->sections['theme_footer_credit']->title, 'Mine' );

check( 'html keeps allowed tags', Sanitize::html( '<em>a</em><iframe></iframe>', [ 'em' => [] ] ), '<em>a</em>' );

echo $failed ? "\n{$failed} failed\n" : "\nAll passed\n";
exit( $failed ? 1 : 0 );
