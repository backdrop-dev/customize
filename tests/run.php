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

echo $failed ? "\n{$failed} failed\n" : "\nAll passed\n";
exit( $failed ? 1 : 0 );
