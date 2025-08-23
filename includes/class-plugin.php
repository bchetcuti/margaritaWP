<?php
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
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
        add_action( 'init', array( $this, 'register_block' ) );
    }
    public function enqueue_frontend() {
        wp_register_style( 'mm-frontend', MM_PLUGIN_URL . 'assets/css/frontend.css', array(), MM_VERSION );
        wp_register_script( 'mm-frontend', MM_PLUGIN_URL . 'assets/js/frontend.js', array( 'jquery' ), MM_VERSION, true );
        wp_localize_script( 'mm-frontend', 'MM_Ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'mm_nonce' ),
        ) );
    }
    public function render_shortcode( $atts = array() ) {
        wp_enqueue_style( 'mm-frontend' );
        wp_enqueue_script( 'mm-frontend' );
        $default_unit   = get_option( 'mm_unit', 'ml' );
        $default_preset = get_option( 'mm_default_preset', 'classic' );
        $max_drinks     = (int) get_option( 'mm_max_drinks', 25 );
        $show_abv       = (bool) get_option( 'mm_show_abv', 1 );
        $presets = $this->calc->list_presets();
        ob_start(); ?>
        <div class="mm-wrap">
            <form class="mm-form" aria-describedby="mm-help" novalidate>
                <div class="mm-row">
                    <label for="mm_preset"><?php echo esc_html__( 'Preset', 'margarita-measurements' ); ?></label>
                    <select id="mm_preset" name="preset">
                        <?php foreach ( $presets as $key => $p ): ?>
                            <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $default_preset ); ?>>
                                <?php echo esc_html( ucfirst( $key ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mm-row">
                    <label for="mm_drinks"><?php echo esc_html__( 'Drinks', 'margarita-measurements' ); ?></label>
                    <input id="mm_drinks" name="drinks" type="number" min="1" max="<?php echo esc_attr( $max_drinks ); ?>" value="1" />
                </div>
                <fieldset class="mm-fieldset">
                    <legend><?php echo esc_html__( 'Units', 'margarita-measurements' ); ?></legend>
                    <?php foreach ( array( 'ml' => 'ml', 'oz' => 'oz', 'shot' => 'Shot', 'nip' => 'Nip' ) as $val => $label ) : ?>
                        <label class="mm-radio">
                            <input type="radio" name="unit" value="<?php echo esc_attr( $val ); ?>" <?php checked( $val, $default_unit ); ?> />
                            <span><?php echo esc_html( $label ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <input type="hidden" name="action" value="mm_calculate" />
                <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'mm_nonce' ) ); ?>" />
                <button type="submit" class="mm-btn"><?php echo esc_html__( 'Calculate', 'margarita-measurements' ); ?></button>
            </form>
            <div id="mm-results" class="mm-results" aria-live="polite"></div>
            <p id="mm-help" class="mm-help"><?php echo esc_html__( 'Tip: switch units or try presets like Tommy’s or Frozen.', 'margarita-measurements' ); ?></p>
        </div>
        <?php return ob_get_clean();
    }
    public function register_block() {
        register_block_type( __DIR__ . '/../block', array(
            'render_callback' => function( $attributes, $content ) {
                return $this->render_shortcode();
            },
        ) );
    }
}
