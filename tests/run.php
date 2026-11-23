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
function __return_true() { return true; }
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

function enable_all(): void {
    foreach ( [ 'layout', 'footer_credit', 'featured_image' ] as $name ) {
        add_filter( "backdrop/customize/{$name}", '__return_true' );
    }
}

$layouts = [
    'left'  => [ 'label' => 'Left',  'url' => '%1$s/left.png' ],
    'right' => [ 'label' => 'Right', 'url' => '%1$s/right.png' ],
];

// With no filters nothing is registered.
reset_filters();
$manager = register( new Component() );
check( 'built-in components are off by default', [ isset( $manager->sections['theme_content_layout'] ), isset( $manager->sections['theme_footer_credit'] ), isset( $manager->sections['theme_content_feature_image'] ), $manager->settings ], [ false, false, false, [] ] );

// Turned on with no other filters: Footer credit and Featured image size are registered, Layout waits for choices.
reset_filters();
enable_all();
$manager = register( new Component() );
check( 'layout waits for choices', isset( $manager->sections['theme_content_layout'] ), false );
check( 'footer credit section', [ $manager->sections['theme_footer_credit']->panel, $manager->sections['theme_footer_credit']->title ], [ 'theme_footer', 'Credit' ] );
check( 'powered by is off by default', $manager->settings['theme_footer_powered_by']['default'], false );
check( 'footer credit uses postMessage', $manager->settings['theme_footer_custom_credit']['transport'], 'postMessage' );
check( 'footer credit has no partial without a callback', $manager->selective_refresh->partials, [] );
check( 'featured image uses postMessage', $manager->settings['theme_content_feature_image']['transport'], 'postMessage' );
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
check( 'layout uses postMessage by default', $manager->settings['theme_content_layout']['transport'], 'postMessage' );
check( 'layout uses the radio image control', $manager->controls['theme_content_layout'] instanceof RadioImageControl, true );

reset_filters();
enable_all();
add_filter( 'backdrop/customize/layout_args', function ( $args ) use ( $layouts ) {
    $args['panel']     = 'theme_global';
    $args['choices']   = $layouts;
    $args['default']   = 'nope';
    $args['transport'] = 'refresh';
    return $args;
} );
$manager = register( new Component() );
check( 'layout can go in the global panel', [ $manager->sections['theme_global_layout']->panel, isset( $manager->sections['theme_content_layout'] ) ], [ 'theme_global', false ] );
check( 'layout default falls back to the first choice', $manager->settings['theme_global_layout']['default'], 'left' );
check( 'layout transport can be changed', $manager->settings['theme_global_layout']['transport'], 'refresh' );

$setting = (object) [ 'id' => 'theme_global_layout', 'manager' => $manager, 'default' => 'left' ];
check( 'layout setting keeps a choice', call_user_func( $manager->settings['theme_global_layout']['sanitize_callback'], 'right', $setting ), 'right' );
check( 'layout setting rejects anything else', call_user_func( $manager->settings['theme_global_layout']['sanitize_callback'], 'nope', $setting ), 'left' );

// Footer credit with a render callback and no generated credit.
reset_filters();
enable_all();
add_filter( 'backdrop/customize/footer_credit_args', function ( $args ) {
    $args['powered_by']      = false;
    $args['credit_default']  = 'Copyright';
    $args['render_callback'] = '__return_false';
    return $args;
} );
$manager = register( new Component() );
check( 'footer credit without the generated credit', [ isset( $manager->settings['theme_footer_powered_by'] ), isset( $manager->controls['theme_footer_powered_by'] ) ], [ false, false ] );
check( 'footer credit default', $manager->settings['theme_footer_custom_credit']['default'], 'Copyright' );
check( 'footer credit with a callback', $manager->settings['theme_footer_custom_credit']['transport'], 'postMessage' );
check( 'footer credit partial', [ $manager->selective_refresh->partials['theme_footer_credit']['selector'], $manager->selective_refresh->partials['theme_footer_credit']['settings'] ], [ '.app-footer__credit', [ 'theme_footer_custom_credit' ] ] );
check( 'footer credit keeps allowed tags only', call_user_func( $manager->settings['theme_footer_custom_credit']['sanitize_callback'], '<b>Hi</b><script>x</script>' ), '<b>Hi</b>x' );

add_filter( 'backdrop/customize/footer_credit_args', function ( $args ) {
    $args['transport'] = 'refresh';
    return $args;
} );
$manager = register( new Component() );
check( 'footer credit transport can be changed', $manager->settings['theme_footer_custom_credit']['transport'], 'refresh' );

// Featured image with the theme's own sizes.
reset_filters();
enable_all();
add_filter( 'backdrop/customize/featured_image_args', function ( $args ) {
    $args['transport'] = 'refresh';
    $args['choices'] = [ 'small' => 'Small', 'large' => 'Large' ];
    $args['default'] = 'large';
    return $args;
} );
$manager = register( new Component() );
check( 'featured image uses the theme sizes', array_keys( $manager->controls['theme_content_feature_image']->choices ), [ 'small', 'large' ] );
check( 'featured image transport can be changed', $manager->settings['theme_content_feature_image']['transport'], 'refresh' );
check( 'featured image default from the theme', $manager->settings['theme_content_feature_image']['default'], 'large' );

// Each built-in component is turned on on its own.
reset_filters();
add_filter( 'backdrop/customize/layout_args', function ( $args ) use ( $layouts ) {
    $args['choices'] = $layouts;
    return $args;
} );
add_filter( 'backdrop/customize/footer_credit', '__return_true' );
$manager = register( new Component() );
check( 'turning one on leaves the others off', [ isset( $manager->sections['theme_content_layout'] ), isset( $manager->sections['theme_footer_credit'] ), isset( $manager->sections['theme_content_feature_image'] ) ], [ false, true, false ] );

// A theme's own component registers after the built-in ones, so it replaces them.
reset_filters();
enable_all();
class ThemeFooter extends Fake {
    public function registerSections( $m ) { $m->add_section( 'theme_footer_credit', [ 'panel' => 'theme_footer', 'title' => 'Mine' ] ); }
}
App::$services[ ThemeFooter::class ] = new ThemeFooter();
$manager = register( new Component( [ ThemeFooter::class ] ) );
check( 'a theme section replaces the built-in one', $manager->sections['theme_footer_credit']->title, 'Mine' );

check( 'html keeps allowed tags', Sanitize::html( '<em>a</em><iframe></iframe>', [ 'em' => [] ] ), '<em>a</em>' );


// Chassis components: a stand-in for Chassis's override layer.
class FakeChassis {
    public static function stylesMap(): array {
        return [
            'styles.color.accent'     => [ 'color-accent', false ],
            'styles.color.text'       => [ 'color-content-text', false ],
            'styles.color.background' => [ 'color-page-background', false ],
            'settings.font.body'      => [ 'font-family-body', false ],
            'settings.font.heading'   => [ 'font-family-heading', false ],
            'styles.typography.size'  => [ 'font-size-base', false ],
            'styles.typography.line-height' => [ 'line-height-base', false ],
        ];
    }
    public $values = [ 'styles.color.accent' => '#334155', 'settings.font.body' => 'Fira Sans, sans-serif' ];
    public function value( string $path ) { return $this->values[ $path ] ?? null; }
    public function overrides( array $values ) { return $this; }
}

function own_sections( WP_Customize_Manager $m ): array {
    return array_values( array_filter( array_keys( $m->sections ), function ( $id ) { return 0 === strpos( $id, 'theme_global_' ); } ) );
}

function enable_chassis(): void {
    foreach ( [ 'colors', 'typography', 'background' ] as $name ) {
        add_filter( "backdrop/customize/{$name}", '__return_true' );
    }
}

reset_filters();
enable_chassis();
unset( App::$services['chassis'] );
$manager = register( new Component() );
check( 'the Chassis components register nothing without Chassis', [ own_sections( $manager ), $manager->settings ], [ [], [] ] );

App::$services['chassis'] = new FakeChassis();
$manager = register( new Component() );
check( 'Colors, Typography and Background add their sections', own_sections( $manager ), [ 'theme_global_colors', 'theme_global_typography', 'theme_global_background' ] );
check( 'the sections go in the Global panel', $manager->sections['theme_global_colors']->panel, 'theme_global' );
check( 'a color starts at the value Chassis has', $manager->settings['theme_color_accent']['default'], '#334155' );
check( 'a color Chassis has no value for starts empty', $manager->settings['theme_color_text']['default'], '' );
check( 'only fields Chassis knows are registered', isset( $manager->settings['theme_color_muted'] ), false );
check( 'the page background is its own setting', isset( $manager->settings['theme_background_color'] ), true );
check( 'the color control is a color control', get_class( $manager->controls['theme_color_accent'] ), 'WP_Customize_Color_Control' );
check( 'Chassis settings preview live by default', $manager->settings['theme_color_accent']['transport'], 'postMessage' );
check( 'the font control shows the theme default font first', array_keys( $manager->controls['theme_type_body']->choices )[0], 'Fira Sans, sans-serif' );
check( 'the font list has the system stacks too', count( $manager->controls['theme_type_body']->choices ), 4 );
check( 'the line height is a number control', $manager->controls['theme_type_line_height']->type, 'number' );

$sanitize = $manager->settings['theme_color_accent']['sanitize_callback'];
check( 'color sanitize keeps a hex color', $sanitize( '#CC3300' ), '#cc3300' );
check( 'color sanitize drops anything else', $sanitize( 'red; background: url(x)' ), '' );
$sanitize = $manager->settings['theme_type_body']['sanitize_callback'];
check( 'font sanitize keeps a listed stack', $sanitize( 'Georgia, "Times New Roman", Times, serif' ), 'Georgia, "Times New Roman", Times, serif' );
check( 'font sanitize drops an unlisted stack', $sanitize( 'x; y' ), '' );
check( 'length sanitize keeps a length', Sanitize::cssLength( '1.125rem' ), '1.125rem' );
check( 'length sanitize drops a bare number', Sanitize::cssLength( '16' ), '' );
check( 'number sanitize keeps a line height', Sanitize::number( '1.6' ), '1.6' );
check( 'number sanitize drops a value out of range', Sanitize::number( '12' ), '' );

// Passing the settings on to Chassis.
$GLOBALS['mods'] = [ 'theme_color_accent' => '#cc3300', 'theme_color_text' => 'nope', 'theme_background_color' => '#ffffff', 'theme_type_size' => '18px', 'theme_type_line_height' => '1.7' ];
$component = new Component();
$overrides = $component->chassisOverrides( [] );
ksort( $overrides );
check( 'changed settings become overrides by path', $overrides, [
    'styles.color.accent'           => '#cc3300',
    'styles.color.background'       => '#ffffff',
    'styles.typography.line-height' => '1.7',
    'styles.typography.size'        => '18px',
] );
check( 'overrides from other sources are kept', $component->chassisOverrides( [ 'x.y' => '1' ] )['x.y'], '1' );
$GLOBALS['mods'] = [];
check( 'settings that were never changed override nothing', $component->chassisOverrides( [] ), [] );

// The live preview.
$GLOBALS['inline'] = [];
$component->chassisPreview();
$script = $GLOBALS['inline']['customize-preview'][0] ?? '';
check( 'the preview script maps each setting to its custom property', ( false !== strpos( $script, '"theme_color_accent":"color-accent"' ) ) && ( false !== strpos( $script, '"theme_background_color":"color-page-background"' ) ), true );

// A theme can change the list of fields.
reset_filters();
enable_chassis();
add_filter( 'backdrop/customize/colors_args', function ( $args ) {
    $args['fields'] = [ 'accent' => [ 'path' => 'styles.color.accent', 'label' => 'Brand' ] ];
    return $args;
} );
$manager = register( new Component() );
check( 'a theme can choose its colors', [ array_keys( $manager->settings ), $manager->controls['theme_color_accent']->label ], [ [ 'theme_color_accent', 'theme_type_body', 'theme_type_heading', 'theme_type_size', 'theme_type_line_height', 'theme_background_color' ], 'Brand' ] );

// Off by default.
reset_filters();
$manager = register( new Component() );
check( 'the Chassis components are off by default', [ own_sections( $manager ), $manager->settings ], [ [], [] ] );

echo $failed ? "\n{$failed} failed\n" : "\nAll passed\n";
exit( $failed ? 1 : 0 );
