<?php
class MM_Settings {
    private static $instance = null;
    public static function instance() {
        if ( null === self::$instance ) { self::$instance = new self(); }
        return self::$instance;
    }
    private function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
    }
    public function add_menu() {
        add_options_page(
            __( 'Margarita Measurements', 'margarita-measurements' ),
            __( 'Margarita Measurements', 'margarita-measurements' ),
            'manage_options',
            'margarita-measurements',
            array( $this, 'render_page' )
        );
    }
    public function register_settings() {
        register_setting( 'mm_settings', 'mm_unit', array( 'type' => 'string', 'sanitize_callback' => array( $this, 'sanitize_unit' ), 'default' => 'ml' ) );
        register_setting( 'mm_settings', 'mm_default_preset', array( 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => 'classic' ) );
        register_setting( 'mm_settings', 'mm_max_drinks', array( 'type' => 'integer', 'sanitize_callback' => 'absint', 'default' => 25 ) );
        register_setting( 'mm_settings', 'mm_show_abv', array( 'type' => 'boolean', 'sanitize_callback' => array( $this, 'sanitize_bool' ), 'default' => 1 ) );
    }
    public function sanitize_unit( $val ) {
        $allowed = array( 'ml', 'oz', 'shot', 'nip' );
        return in_array( $val, $allowed, true ) ? $val : 'ml';
    }
    public function sanitize_bool( $val ) { return $val ? 1 : 0; }
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Margarita Measurements Settings', 'margarita-measurements' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'mm_settings' ); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="mm_unit"><?php esc_html_e( 'Default Unit', 'margarita-measurements' ); ?></label></th>
                        <td>
                            <select id="mm_unit" name="mm_unit">
                                <?php foreach ( array( 'ml','oz','shot','nip' ) as $u ): ?>
                                    <option value="<?php echo esc_attr( $u ); ?>" <?php selected( get_option('mm_unit', 'ml'), $u ); ?>>
                                        <?php echo esc_html( strtoupper( $u ) ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mm_default_preset"><?php esc_html_e( 'Default Preset', 'margarita-measurements' ); ?></label></th>
                        <td>
                            <select id="mm_default_preset" name="mm_default_preset">
                                <?php foreach ( array( 'classic','tommys','frozen','skinny' ) as $p ): ?>
                                    <option value="<?php echo esc_attr( $p ); ?>" <?php selected( get_option('mm_default_preset', 'classic'), $p ); ?>>
                                        <?php echo esc_html( ucfirst( $p ) ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mm_max_drinks"><?php esc_html_e( 'Max Drinks (per calc)', 'margarita-measurements' ); ?></label></th>
                        <td><input type="number" id="mm_max_drinks" name="mm_max_drinks" min="1" max="200" value="<?php echo esc_attr( get_option('mm_max_drinks', 25) ); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Show ABV', 'margarita-measurements' ); ?></th>
                        <td><input type="checkbox" name="mm_show_abv" value="1" <?php checked( get_option('mm_show_abv', 1 ), 1 ); ?> /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
