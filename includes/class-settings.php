<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

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
        register_setting( 'mm_settings', 'mm_standard_drink_region', array( 'type' => 'string', 'sanitize_callback' => array( $this, 'sanitize_standard_drink_region' ), 'default' => 'AU' ) );
        register_setting( 'mm_settings', 'mm_bottle_sizes', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_bottle_sizes' ), 'default' => array() ) );
        register_setting( 'mm_custom_presets_settings', 'mm_custom_presets', array( 'type' => 'array', 'sanitize_callback' => array( $this, 'sanitize_custom_presets' ), 'default' => array() ) );
    }
    public function sanitize_unit( $val ) {
        $allowed = array( 'ml', 'oz', 'shot', 'nip' );
        return in_array( $val, $allowed, true ) ? $val : 'ml';
    }
    public function sanitize_bool( $val ) { return $val ? 1 : 0; }
    public function sanitize_standard_drink_region( $val ) {
        $allowed = array( 'AU', 'US', 'UK' );
        $val = strtoupper( sanitize_key( strtolower( (string) $val ) ) );
        return in_array( $val, $allowed, true ) ? $val : 'AU';
    }
    public function sanitize_bottle_sizes( $value ) {
        $defaults = array( 'tequila' => 700, 'triple' => 700, 'citrus' => 1000, 'agave' => 350, 'mixer' => 1000 );
        $clean = array();
        $value = is_array( $value ) ? $value : array();
        foreach ( $defaults as $key => $default ) {
            $clean[ $key ] = min( 5000, max( 50, (float) ( $value[ $key ] ?? $default ) ) );
        }
        return $clean;
    }
    public function sanitize_custom_presets( $value ) {
        $existing = get_option( 'mm_custom_presets', array() );
        $presets  = is_array( $existing ) ? $existing : array();

        if ( ! isset( $_POST['mm_custom_preset_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mm_custom_preset_nonce'] ) ), 'mm_custom_preset_nonce' ) ) {
            return array_slice( $presets, 0, 20, true );
        }

        $new = is_array( $value ) && isset( $value['new'] ) && is_array( $value['new'] ) ? $value['new'] : array();
        $label = sanitize_text_field( wp_unslash( $new['label'] ?? '' ) );
        if ( '' === $label || count( $presets ) >= 20 ) {
            return array_slice( $presets, 0, 20, true );
        }

        $key = sanitize_key( $label );
        if ( '' === $key ) {
            return array_slice( $presets, 0, 20, true );
        }

        $presets[ $key ] = array(
            'label'      => $label,
            'tequila_ml' => max( 0.0, (float) ( $new['tequila_ml'] ?? 0 ) ),
            'citrus_ml'  => max( 0.0, (float) ( $new['citrus_ml'] ?? 0 ) ),
            'triple_ml'  => max( 0.0, (float) ( $new['triple_ml'] ?? 0 ) ),
            'agave_ml'   => max( 0.0, (float) ( $new['agave_ml'] ?? 0 ) ),
            'ice_factor' => max( 0.0, (float) ( $new['ice_factor'] ?? 1.0 ) ),
            'triple_abv' => min( 1.0, max( 0.0, (float) ( $new['triple_abv'] ?? 40 ) / 100 ) ),
            'note'       => sanitize_text_field( wp_unslash( $new['note'] ?? '' ) ),
        );

        return array_slice( $presets, 0, 20, true );
    }
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
                                <?php foreach ( MM_Plugin::instance()->calc->list_presets() as $p => $preset ): ?>
                                    <option value="<?php echo esc_attr( $p ); ?>" <?php selected( get_option('mm_default_preset', 'classic'), $p ); ?>>
                                        <?php echo esc_html( $preset['label'] ?? ucfirst( $p ) ); ?>
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
                    <tr>
                        <th scope="row"><label for="mm_standard_drink_region"><?php esc_html_e( 'Standard drink default', 'margarita-measurements' ); ?></label></th>
                        <td><select id="mm_standard_drink_region" name="mm_standard_drink_region"><?php foreach ( array( 'AU' => 'Australia (10g)', 'US' => 'United States (14g)', 'UK' => 'United Kingdom (8g)' ) as $region => $label ) : ?><option value="<?php echo esc_attr( $region ); ?>" <?php selected( get_option( 'mm_standard_drink_region', 'AU' ), $region ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td>
                    </tr>
                    <?php $mm_bottle_sizes = MM_Plugin::instance()->calc->bottle_sizes(); ?>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Party bottle sizes (ml)', 'margarita-measurements' ); ?></th>
                        <td class="mm-bottle-size-settings">
                            <?php foreach ( array( 'tequila' => 'Tequila', 'triple' => 'Triple sec', 'citrus' => 'Citrus juice', 'agave' => 'Agave syrup', 'mixer' => 'Flavour mixers' ) as $key => $label ) : ?>
                                <label><?php echo esc_html( $label ); ?> <input type="number" name="mm_bottle_sizes[<?php echo esc_attr( $key ); ?>]" min="50" max="5000" step="50" value="<?php echo esc_attr( $mm_bottle_sizes[ $key ] ); ?>" /></label><br />
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr />
            <h2><?php esc_html_e( 'Custom Presets', 'margarita-measurements' ); ?></h2>
            <?php $custom_presets = get_option( 'mm_custom_presets', array() ); $custom_presets = is_array( $custom_presets ) ? $custom_presets : array(); ?>
            <table class="widefat striped" style="max-width: 900px;">
                <thead><tr><th><?php esc_html_e( 'Name', 'margarita-measurements' ); ?></th><th><?php esc_html_e( 'Ratios', 'margarita-measurements' ); ?></th><th><?php esc_html_e( 'Action', 'margarita-measurements' ); ?></th></tr></thead>
                <tbody>
                    <?php if ( empty( $custom_presets ) ) : ?>
                        <tr><td colspan="3"><?php esc_html_e( 'No custom presets yet.', 'margarita-measurements' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $custom_presets as $key => $preset ) : ?>
                            <tr>
                                <td><?php echo esc_html( $preset['label'] ?? ucfirst( $key ) ); ?></td>
                                <td><?php echo esc_html( sprintf( 'Tequila %s ml, Citrus %s ml, Triple %s ml, Agave %s ml, Triple ABV %s%%, Ice x%s', $preset['tequila_ml'] ?? 0, $preset['citrus_ml'] ?? 0, $preset['triple_ml'] ?? 0, $preset['agave_ml'] ?? 0, round( ( $preset['triple_abv'] ?? 0.4 ) * 100, 1 ), $preset['ice_factor'] ?? 1 ) ); ?></td>
                                <td><button type="button" class="button mm-delete-preset" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Delete', 'margarita-measurements' ); ?></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <h3><?php esc_html_e( 'Add preset', 'margarita-measurements' ); ?></h3>
            <form method="post" action="options.php">
                <?php settings_fields( 'mm_custom_presets_settings' ); wp_nonce_field( 'mm_custom_preset_nonce', 'mm_custom_preset_nonce' ); ?>
                <table class="form-table" role="presentation">
                    <tr><th><label for="mm_custom_label"><?php esc_html_e( 'Label', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_label" name="mm_custom_presets[new][label]" type="text" required /></td></tr>
                    <tr><th><label for="mm_custom_tequila"><?php esc_html_e( 'Tequila ml', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_tequila" name="mm_custom_presets[new][tequila_ml]" type="number" min="0" step="0.1" value="60" /></td></tr>
                    <tr><th><label for="mm_custom_citrus"><?php esc_html_e( 'Citrus ml', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_citrus" name="mm_custom_presets[new][citrus_ml]" type="number" min="0" step="0.1" value="25" /></td></tr>
                    <tr><th><label for="mm_custom_triple"><?php esc_html_e( 'Triple sec ml', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_triple" name="mm_custom_presets[new][triple_ml]" type="number" min="0" step="0.1" value="15" /></td></tr>
                    <tr><th><label for="mm_custom_agave"><?php esc_html_e( 'Agave ml', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_agave" name="mm_custom_presets[new][agave_ml]" type="number" min="0" step="0.1" value="5" /></td></tr>
                    <tr><th><label for="mm_custom_abv"><?php esc_html_e( 'Triple sec ABV %', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_abv" name="mm_custom_presets[new][triple_abv]" type="number" min="0" max="100" step="0.1" value="40" /></td></tr>
                    <tr><th><label for="mm_custom_ice"><?php esc_html_e( 'Ice factor', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_ice" name="mm_custom_presets[new][ice_factor]" type="number" min="0" step="0.1" value="1.0" /></td></tr>
                    <tr><th><label for="mm_custom_note"><?php esc_html_e( 'Note', 'margarita-measurements' ); ?></label></th><td><input id="mm_custom_note" name="mm_custom_presets[new][note]" type="text" /></td></tr>
                </table>
                <?php submit_button( __( 'Save Custom Preset', 'margarita-measurements' ) ); ?>
            </form>
            <script>
            jQuery(function($){
                $('.mm-delete-preset').on('click', function(){
                    if (!window.confirm('<?php echo esc_js( __( 'Delete this preset?', 'margarita-measurements' ) ); ?>')) { return; }
                    $.post(ajaxurl, { action: 'mm_delete_preset', nonce: '<?php echo esc_js( wp_create_nonce( 'mm_delete_preset_nonce' ) ); ?>', preset_key: $(this).data('key') }, function(){ window.location.reload(); });
                });
            });
            </script>
        </div>
        <?php
    }
}
