<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MM_Plugin {
    private static $instance = null;
    public $calc;
    public static function instance() {
        if ( null === self::$instance ) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {
        $this->calc = new MM_Calculator();
        add_shortcode( 'margarita_measurements', array( $this, 'render_shortcode' ) );
        add_shortcode( 'margarita_widget', array( $this, 'render_widget_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
        add_action( 'init', array( $this, 'register_block' ) );
    }
    public function enqueue_frontend() {
        wp_register_style( 'mm-frontend', MM_PLUGIN_URL . 'assets/css/frontend.css', array(), MM_VERSION );
        wp_register_style( 'mm-print', MM_PLUGIN_URL . 'assets/css/print.css', array(), MM_VERSION, 'print' );
        wp_register_script( 'mm-frontend', MM_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery' ), MM_VERSION, true );
        wp_localize_script( 'mm-frontend', 'MM_Ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mm_nonce' ),
        ) );
    }
    protected function sanitize_bool( $value, $fallback = true ) {
        if ( is_bool( $value ) ) { return $value; }
        if ( '' === $value || null === $value ) { return $fallback; }
        return in_array( strtolower( (string) $value ), array( '1', 'true', 'yes', 'on' ), true );
    }
    protected function sanitize_align( $align ) {
        $align = sanitize_key( $align );
        return in_array( $align, array( 'none', 'right', 'left', 'center' ), true ) ? $align : 'none';
    }

    public function sanitize_render_atts( $atts = array(), $shortcode = 'margarita_measurements' ) {
        $default_unit   = get_option( 'mm_unit', 'ml' );
        $default_preset = get_option( 'mm_default_preset', 'classic' );
        $max_drinks     = (int) get_option( 'mm_max_drinks', 25 );
        $admin_show_abv = (bool) get_option( 'mm_show_abv', 1 );
        $presets        = $this->calc->list_presets();
        $allowed_units  = array( 'ml', 'oz', 'shot', 'nip' );
        $allowed_modes  = array( 'drinks', 'pitcher', 'party' );
        $is_widget      = 'margarita_widget' === $shortcode;

        $atts = shortcode_atts(
            array(
                'preset'         => $default_preset,
                'unit'           => $default_unit,
                'flavour'        => 'none',
                'drinks'         => 1,
                'show_abv'       => $is_widget ? 'false' : ( $admin_show_abv ? 'true' : 'false' ),
                'show_nutrition' => $is_widget ? 'false' : 'true',
                'mode'           => 'drinks',
                'title'          => $is_widget ? __( 'Margarita calculator', 'margarita-measurements' ) : __( 'Margarita Measurements', 'margarita-measurements' ),
                'tequila_abv'    => 40,
                'triple_sec_abv' => 40,
                'standard_region'=> 'AU',
                'align'          => 'none',
            ),
            $atts,
            $shortcode
        );

        $preset = sanitize_key( $atts['preset'] );
        $preset = isset( $presets[ $preset ] ) ? $preset : ( isset( $presets[ $default_preset ] ) ? $default_preset : 'classic' );
        $unit   = sanitize_key( $atts['unit'] );
        $unit   = in_array( $unit, $allowed_units, true ) ? $unit : ( in_array( $default_unit, $allowed_units, true ) ? $default_unit : 'ml' );
        $mode   = $is_widget ? 'drinks' : sanitize_key( $atts['mode'] );
        $mode   = in_array( $mode, $allowed_modes, true ) ? $mode : 'drinks';
        $drinks = min( max( 1, absint( $atts['drinks'] ) ), max( 1, $max_drinks ) );
        $title  = sanitize_text_field( $atts['title'] );

        return array(
            'preset'          => $preset,
            'unit'            => $unit,
            'flavour'         => $this->calc->normalise_flavour_key( $atts['flavour'] ),
            'drinks'          => $drinks,
            'show_abv'        => $this->sanitize_bool( $atts['show_abv'], ! $is_widget && $admin_show_abv ),
            'show_nutrition'  => $this->sanitize_bool( $atts['show_nutrition'], ! $is_widget ),
            'mode'            => $mode,
            'title'           => '' === $title ? ( $is_widget ? __( 'Margarita calculator', 'margarita-measurements' ) : __( 'Margarita Measurements', 'margarita-measurements' ) ) : $title,
            'tequila_abv'     => $this->calc->sanitize_tequila_abv( $atts['tequila_abv'] ),
            'triple_sec_abv'  => $this->calc->sanitize_triple_sec_abv( $atts['triple_sec_abv'] ),
            'standard_region' => $this->calc->normalise_standard_drink_region( $atts['standard_region'] ),
            'align'           => $this->sanitize_align( $atts['align'] ),
        );
    }

    protected function block_attributes_to_shortcode_atts( $attributes ) {
        $atts = array();
        foreach ( array( 'preset', 'unit', 'flavour', 'mode', 'title' ) as $key ) {
            if ( isset( $attributes[ $key ] ) ) { $atts[ $key ] = $attributes[ $key ]; }
        }
        if ( array_key_exists( 'showAbv', $attributes ) ) { $atts['show_abv'] = $attributes['showAbv'] ? 'true' : 'false'; }
        return $atts;
    }

    public function render_widget_shortcode( $atts = array() ) {
        return $this->render_shortcode( $atts, 'widget' );
    }

    public function render_shortcode( $atts = array(), $context = 'full' ) {
        wp_enqueue_style( 'mm-frontend' );
        wp_enqueue_style( 'mm-print' );
        wp_enqueue_script( 'mm-frontend' );

        $is_widget = 'widget' === $context;
        $shortcode = $is_widget ? 'margarita_widget' : 'margarita_measurements';
        $max_drinks = (int) get_option( 'mm_max_drinks', 25 );
        $presets   = $this->calc->list_presets();
        $flavours  = $this->calc->list_flavours();
        $atts      = $this->sanitize_render_atts( $atts, $shortcode );
        $instance  = wp_unique_id( 'mm-' );
        $wrap_classes = array( 'mm-wrap' );
        if ( $is_widget ) {
            $wrap_classes[] = 'mm-widget';
            $wrap_classes[] = 'mm-widget--align-' . $atts['align'];
        }
        ob_start(); ?>
        <div class="<?php echo esc_attr( implode( ' ', $wrap_classes ) ); ?>">
            <form class="mm-form" aria-describedby="<?php echo esc_attr( $instance ); ?>-help" novalidate>
                <h2 class="<?php echo esc_attr( $is_widget ? 'mm-widget__title' : 'mm-title' ); ?>"><?php echo esc_html( $atts['title'] ); ?></h2>
                <div class="mm-row">
                    <label for="<?php echo esc_attr( $instance ); ?>-preset"><?php esc_html_e( 'Preset', 'margarita-measurements' ); ?></label>
                    <select id="<?php echo esc_attr( $instance ); ?>-preset" name="preset"><?php foreach ( $presets as $key => $p ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $atts['preset'] ); ?>><?php echo esc_html( $p['label'] ?? ucfirst( $key ) ); ?></option><?php endforeach; ?></select>
                </div>
                <div class="mm-row">
                    <label for="<?php echo esc_attr( $instance ); ?>-flavour"><?php esc_html_e( 'Flavour', 'margarita-measurements' ); ?></label>
                    <select id="<?php echo esc_attr( $instance ); ?>-flavour" name="flavour"><?php foreach ( $flavours as $key => $f ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $atts['flavour'] ); ?>><?php echo esc_html( $f['label'] ); ?></option><?php endforeach; ?></select>
                </div>
                <div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-drinks"><?php esc_html_e( 'Drinks', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-drinks" name="drinks" type="number" min="1" max="<?php echo esc_attr( $max_drinks ); ?>" value="<?php echo esc_attr( $atts['drinks'] ); ?>" /></div>
                <?php if ( ! $is_widget ) : ?>
                    <div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-mode"><?php esc_html_e( 'Mode', 'margarita-measurements' ); ?></label><select id="<?php echo esc_attr( $instance ); ?>-mode" name="mode"><option value="drinks" <?php selected( 'drinks', $atts['mode'] ); ?>><?php esc_html_e( 'Per drink count', 'margarita-measurements' ); ?></option><option value="pitcher" <?php selected( 'pitcher', $atts['mode'] ); ?>><?php esc_html_e( 'Pitcher (total ml)', 'margarita-measurements' ); ?></option><option value="party" <?php selected( 'party', $atts['mode'] ); ?>><?php esc_html_e( 'Party Planning', 'margarita-measurements' ); ?></option></select></div>
                    <div class="mm-row mm-pitcher-row"<?php echo 'pitcher' === $atts['mode'] ? '' : ' style="display:none;"'; ?>><label for="<?php echo esc_attr( $instance ); ?>-pitcher"><?php esc_html_e( 'Pitcher size (ml)', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-pitcher" name="pitcher_ml" type="number" min="100" max="5000" step="50" value="1000" /></div>
                    <div class="mm-party-fields"<?php echo 'party' === $atts['mode'] ? '' : ' style="display:none;"'; ?>><div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-guests"><?php esc_html_e( 'Guests', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-guests" name="guests" type="number" min="1" max="500" value="10" /></div><div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-drinks-person"><?php esc_html_e( 'Drinks per person', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-drinks-person" name="drinks_per_person" type="number" min="0.1" max="12" step="0.1" value="2" /></div><div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-duration"><?php esc_html_e( 'Event duration (hours)', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-duration" name="event_duration" type="number" min="0.5" max="24" step="0.5" value="2" /></div></div>
                    <details class="mm-details mm-advanced"><summary><?php esc_html_e( 'Advanced', 'margarita-measurements' ); ?></summary><div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-tequila-abv"><?php esc_html_e( 'Tequila ABV %', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-tequila-abv" name="tequila_abv" type="number" min="35" max="55" step="0.1" value="<?php echo esc_attr( $atts['tequila_abv'] ); ?>" /></div><div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-triple-sec-abv"><?php esc_html_e( 'Triple sec ABV %', 'margarita-measurements' ); ?></label><input id="<?php echo esc_attr( $instance ); ?>-triple-sec-abv" name="triple_sec_abv" type="number" min="15" max="45" step="0.1" value="<?php echo esc_attr( $atts['triple_sec_abv'] ); ?>" /></div><div class="mm-row"><label for="<?php echo esc_attr( $instance ); ?>-standard-region"><?php esc_html_e( 'Standard drink', 'margarita-measurements' ); ?></label><select id="<?php echo esc_attr( $instance ); ?>-standard-region" name="standard_region"><?php foreach ( $this->calc->standard_drink_regions() as $region => $details ) : ?><option value="<?php echo esc_attr( $region ); ?>" <?php selected( $atts['standard_region'], $region ); ?>><?php echo esc_html( $details['label'] ); ?></option><?php endforeach; ?></select></div></details>
                    <div class="mm-row"><label><input type="checkbox" name="wet_rim" value="1" checked /> <?php esc_html_e( 'Wet rim (more salt)', 'margarita-measurements' ); ?></label></div>
                <?php else : ?>
                    <input type="hidden" name="mode" value="drinks" /><input type="hidden" name="tequila_abv" value="<?php echo esc_attr( $atts['tequila_abv'] ); ?>" /><input type="hidden" name="triple_sec_abv" value="<?php echo esc_attr( $atts['triple_sec_abv'] ); ?>" /><input type="hidden" name="standard_region" value="<?php echo esc_attr( $atts['standard_region'] ); ?>" />
                <?php endif; ?>
                <fieldset class="mm-fieldset"><legend><?php esc_html_e( 'Units', 'margarita-measurements' ); ?></legend><?php foreach ( array( 'ml' => 'ml', 'oz' => 'oz', 'shot' => 'Shot', 'nip' => 'Nip' ) as $val => $label ) : ?><label class="mm-radio"><input type="radio" name="unit" value="<?php echo esc_attr( $val ); ?>" <?php checked( $val, $atts['unit'] ); ?> /> <span><?php echo esc_html( $label ); ?></span></label><?php endforeach; ?></fieldset>
                <input type="hidden" name="action" value="mm_calculate" /><input type="hidden" name="show_abv" value="<?php echo esc_attr( $atts['show_abv'] ? '1' : '0' ); ?>" /><input type="hidden" name="show_nutrition" value="<?php echo esc_attr( $atts['show_nutrition'] ? '1' : '0' ); ?>" /><input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'mm_nonce' ) ); ?>" />
                <button type="submit" class="mm-btn"><?php esc_html_e( 'Calculate', 'margarita-measurements' ); ?></button>
            </form>
            <div class="mm-results<?php echo $is_widget ? ' mm-widget__result' : ''; ?>" aria-live="polite"></div>
            <?php if ( ! $is_widget ) : ?><p id="<?php echo esc_attr( $instance ); ?>-help" class="mm-help"><?php esc_html_e( 'Tip: switch units, add a flavour, or try presets like Tommy’s or Frozen.', 'margarita-measurements' ); ?></p><?php endif; ?>
        </div>
        <?php return ob_get_clean();
    }

    public function register_block() {
        register_block_type( __DIR__ . '/../block', array(
            'render_callback' => function( $attributes, $content ) {
                return $this->render_shortcode( $this->block_attributes_to_shortcode_atts( is_array( $attributes ) ? $attributes : array() ) );
            },
        ) );
    }
}
