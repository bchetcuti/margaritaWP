<?php
class MM_Calculator {
    protected $presets = array(
        'classic' => array(
            'label'        => 'Classic',
            'tequila_ml'   => 45.0,
            'citrus_ml'    => 30.0,
            'triple_ml'    => 15.0,
            'agave_ml'     => 0.0,
            'ice_factor'   => 1.0,
            'triple_abv'   => 0.40,
            'note'         => 'Classic 3:2:1',
        ),
        'tommys' => array(
            'label'        => 'Tommy’s',
            'tequila_ml'   => 60.0,
            'citrus_ml'    => 30.0,
            'triple_ml'    => 0.0,
            'agave_ml'     => 7.5,
            'ice_factor'   => 1.0,
            'triple_abv'   => 0.0,
            'note'         => "Tommy's (no triple sec)",
        ),
        'frozen' => array(
            'label'        => 'Frozen',
            'tequila_ml'   => 50.0,
            'citrus_ml'    => 30.0,
            'triple_ml'    => 20.0,
            'agave_ml'     => 0.0,
            'ice_factor'   => 1.5,
            'triple_abv'   => 0.40,
            'note'         => 'Frozen (blended)',
        ),
        'skinny' => array(
            'label'        => 'Skinny',
            'tequila_ml'   => 50.0,
            'citrus_ml'    => 35.0,
            'triple_ml'    => 7.5,
            'agave_ml'     => 0.0,
            'ice_factor'   => 1.0,
            'triple_abv'   => 0.30,
            'note'         => 'Skinny (lighter liqueur)',
        ),
    );

    public function list_presets() {
        $custom = get_option( 'mm_custom_presets', array() );
        return array_merge( $this->presets, is_array( $custom ) ? $custom : array() );
    }


    public function list_flavours() {
        return array(
            'none'       => array( 'label' => __( 'None', 'margarita-measurements' ) ),
            'spicy'      => array( 'label' => __( 'Spicy', 'margarita-measurements' ), 'tequila_label' => __( 'Jalapeño tequila', 'margarita-measurements' ), 'heat_modifier' => 0.05 ),
            'mango'      => array( 'label' => __( 'Mango', 'margarita-measurements' ), 'ingredient_key' => 'mango_nectar', 'ingredient_label' => __( 'Mango nectar', 'margarita-measurements' ), 'ingredient_ml' => 30.0 ),
            'watermelon' => array( 'label' => __( 'Watermelon', 'margarita-measurements' ), 'ingredient_key' => 'watermelon_juice', 'ingredient_label' => __( 'Watermelon juice', 'margarita-measurements' ), 'ingredient_ml' => 40.0, 'citrus_delta_ml' => -10.0 ),
            'strawberry' => array( 'label' => __( 'Strawberry', 'margarita-measurements' ), 'ingredient_key' => 'strawberry_puree', 'ingredient_label' => __( 'Strawberry purée', 'margarita-measurements' ), 'ingredient_ml' => 25.0 ),
            'coconut'    => array( 'label' => __( 'Coconut', 'margarita-measurements' ), 'ingredient_key' => 'coconut_cream', 'ingredient_label' => __( 'Coconut cream', 'margarita-measurements' ), 'ingredient_ml' => 20.0, 'agave_override_ml' => 0.0 ),
            'virgin'     => array( 'label' => __( 'Virgin', 'margarita-measurements' ), 'tequila_label' => __( 'Sparkling water', 'margarita-measurements' ), 'replace_tequila' => true, 'no_alcohol' => true ),
        );
    }

    public function normalise_flavour_key( $key ) {
        $key      = sanitize_key( $key );
        $flavours = $this->list_flavours();
        return isset( $flavours[ $key ] ) ? $key : 'none';
    }

    public function get_flavour( $key ) {
        $flavours = $this->list_flavours();
        $key      = $this->normalise_flavour_key( $key );
        return $flavours[ $key ];
    }

    public function ml_to_unit( $ml, $unit ) {
        switch ( $unit ) {
            case 'oz': return $ml / 29.5735;
            case 'shot': return $ml / 44.0;
            case 'nip': return $ml / 30.0;
            case 'ml':
            default: return $ml;
        }
    }

    public function unit_suffix( $unit ) {
        switch ( $unit ) {
            case 'oz': return 'oz';
            case 'shot': return 'shot(s)';
            case 'nip': return 'nip(s)';
            case 'ml':
            default: return 'ml';
        }
    }

    public function sanitize_tequila_abv( $value ) {
        if ( ! is_numeric( $value ) ) {
            return 40.0;
        }
        return min( 55.0, max( 35.0, (float) $value ) );
    }

    public function sanitize_triple_sec_abv( $value ) {
        if ( ! is_numeric( $value ) ) {
            return 40.0;
        }
        return min( 45.0, max( 15.0, (float) $value ) );
    }

    public function normalise_abv_overrides( $args = array(), $preset = array() ) {
        $preset_triple_abv = isset( $preset['triple_abv'] ) ? ( (float) $preset['triple_abv'] * 100 ) : 40.0;
        return array(
            'tequila_abv'    => $this->sanitize_tequila_abv( $args['tequila_abv'] ?? 40.0 ),
            'triple_sec_abv' => $this->sanitize_triple_sec_abv( $args['triple_sec_abv'] ?? $preset_triple_abv ),
        );
    }

    public function calculate_alcohol_ml( $quantities, $abv_overrides = array() ) {
        $tequila_abv    = $this->sanitize_tequila_abv( $abv_overrides['tequila_abv'] ?? 40.0 ) / 100;
        $triple_sec_abv = $this->sanitize_triple_sec_abv( $abv_overrides['triple_sec_abv'] ?? 40.0 ) / 100;
        $tequila_ml     = isset( $quantities['tequila']['ml'] ) ? (float) $quantities['tequila']['ml'] : 0.0;
        $triple_ml      = isset( $quantities['triple']['ml'] ) ? (float) $quantities['triple']['ml'] : 0.0;
        return ( $tequila_ml * $tequila_abv ) + ( $triple_ml * $triple_sec_abv );
    }

    public function calculate_alcohol_grams( $alcohol_ml ) {
        return max( 0.0, (float) $alcohol_ml ) * 0.789;
    }

    public function calculate_standard_drinks( $alcohol_grams, $region ) {
        $regions = $this->standard_drink_regions();
        $region  = $this->normalise_standard_drink_region( $region );
        $grams   = $regions[ $region ]['grams'];
        return $grams > 0 ? (float) $alcohol_grams / $grams : 0.0;
    }

    public function calculate_calories( $quantities, $abv_overrides = array() ) {
        $alcohol_grams   = $this->calculate_alcohol_grams( $this->calculate_alcohol_ml( $quantities, $abv_overrides ) );
        $alcohol_kcal    = $alcohol_grams * 7;
        $sweetener_ml    = isset( $quantities['agave']['ml'] ) ? (float) $quantities['agave']['ml'] : 0.0;
        $sweetener_kcal  = $sweetener_ml * 3.1;
        return array(
            'total_kcal'     => $alcohol_kcal + $sweetener_kcal,
            'alcohol_kcal'   => $alcohol_kcal,
            'sweetener_kcal' => $sweetener_kcal,
            'includes'       => $sweetener_ml > 0 ? __( 'Includes alcohol and sweetener estimate.', 'margarita-measurements' ) : __( 'Alcohol calories only.', 'margarita-measurements' ),
        );
    }

    public function abv_estimate( $tequila_ml, $triple_ml, $total_ml, $triple_abv = 0.40, $tequila_abv = 0.40 ) {
        $alc_ml = ( $tequila_ml * $tequila_abv ) + ( $triple_ml * $triple_abv );
        if ( $total_ml <= 0 ) {
            return 0;
        }
        return ( $alc_ml / $total_ml ) * 100.0;
    }

    public function salt_rim( $drinks, $wet = true ) {
        $g_per_rim = $wet ? 1.5 : 1.0;
        $total_g   = $drinks * $g_per_rim;
        return array(
            'drinks'  => $drinks,
            'wet_dry' => $wet ? 'wet' : 'dry',
            'grams'   => round( $total_g, 1 ),
            'tsps'    => round( $total_g / 6.0, 1 ),
        );
    }

    protected function apply_flavour( $amounts, $flavour_key, $scale ) {
        $flavour_key = $this->normalise_flavour_key( $flavour_key );
        $flavour     = $this->get_flavour( $flavour_key );
        $extra_ml    = 0.0;
        $extra       = null;

        if ( ! empty( $flavour['replace_tequila'] ) ) {
            $amounts['tequila_ml'] = 0.0;
            $amounts['triple_ml']  = 0.0;
        }

        if ( isset( $flavour['citrus_delta_ml'] ) ) {
            $amounts['citrus_ml'] = max( 0.0, $amounts['citrus_ml'] + ( (float) $flavour['citrus_delta_ml'] * $scale ) );
        }

        if ( isset( $flavour['agave_override_ml'] ) ) {
            $amounts['agave_ml'] = (float) $flavour['agave_override_ml'] * $scale;
        }

        if ( isset( $flavour['ingredient_key'], $flavour['ingredient_ml'] ) ) {
            $extra_ml = (float) $flavour['ingredient_ml'] * $scale;
            $extra    = array(
                'key'     => $flavour['ingredient_key'],
                'label'   => $flavour['ingredient_label'] ?? $flavour['label'],
                'ml'      => $extra_ml,
                'display' => 0,
            );
        }

        if ( ! empty( $flavour['replace_tequila'] ) ) {
            $extra_ml = $amounts['original_tequila_ml'] + $amounts['original_triple_ml'];
            $extra    = array(
                'key'     => 'sparkling_water',
                'label'   => $flavour['tequila_label'] ?? __( 'Sparkling water', 'margarita-measurements' ),
                'ml'      => $extra_ml,
                'display' => 0,
            );
        }

        $amounts['extra_ml'] = $extra_ml;
        $amounts['extra']    = $extra;
        return $amounts;
    }

    protected function flavour_meta( $flavour_key ) {
        $flavour_key = $this->normalise_flavour_key( $flavour_key );
        $flavour     = $this->get_flavour( $flavour_key );
        return array(
            'key'           => $flavour_key,
            'label'         => $flavour['label'],
            'tequila_label' => $flavour['tequila_label'] ?? __( 'Tequila', 'margarita-measurements' ),
            'heat_modifier' => $flavour['heat_modifier'] ?? 0,
            'no_alcohol'    => ! empty( $flavour['no_alcohol'] ),
        );
    }


    public function standard_drink_regions() {
        return array(
            'AU' => array( 'label' => __( 'Australia (10g)', 'margarita-measurements' ), 'grams' => 10.0 ),
            'UK' => array( 'label' => __( 'United Kingdom (8g)', 'margarita-measurements' ), 'grams' => 8.0 ),
            'US' => array( 'label' => __( 'United States (14g)', 'margarita-measurements' ), 'grams' => 14.0 ),
        );
    }

    public function normalise_standard_drink_region( $region ) {
        $region  = strtoupper( sanitize_key( strtolower( (string) $region ) ) );
        $regions = $this->standard_drink_regions();
        return isset( $regions[ $region ] ) ? $region : 'AU';
    }

    protected function add_nutrition( $result, $args, $abv_overrides ) {
        $region          = $this->normalise_standard_drink_region( $args['standard_region'] ?? $args['standard_drink_region'] ?? 'AU' );
        $regions         = $this->standard_drink_regions();
        $alcohol_ml      = $this->calculate_alcohol_ml( $result['quantities'], $abv_overrides );
        $alcohol_grams   = $this->calculate_alcohol_grams( $alcohol_ml );
        $standard_drinks = $this->calculate_standard_drinks( $alcohol_grams, $region );
        $calories        = $this->calculate_calories( $result['quantities'], $abv_overrides );
        $divisor         = isset( $result['drinks'] ) ? max( 1, (int) $result['drinks'] ) : 1;
        $result['nutrition'] = array(
            'region'                => $region,
            'region_label'          => $regions[ $region ]['label'],
            'standard_drink_grams'  => $regions[ $region ]['grams'],
            'alcohol_ml'            => round( $alcohol_ml, 2 ),
            'alcohol_grams'         => round( $alcohol_grams, 2 ),
            'alcohol_grams_per_drink' => round( $alcohol_grams / $divisor, 2 ),
            'standard_drinks'       => round( $standard_drinks, 2 ),
            'standard_drinks_per_drink' => round( $standard_drinks / $divisor, 2 ),
            'calories'              => round( $calories['total_kcal'] ),
            'calories_per_drink'    => round( $calories['total_kcal'] / $divisor ),
            'calorie_note'          => $calories['includes'],
            'help'                  => __( 'Approximate values for recipe planning only.', 'margarita-measurements' ),
        );
        return $result;
    }

    public function bottle_sizes() {
        $defaults = array(
            'tequila' => 700.0,
            'triple'  => 700.0,
            'citrus'  => 1000.0,
            'agave'   => 350.0,
            'mixer'   => 1000.0,
        );
        $saved = get_option( 'mm_bottle_sizes', array() );
        if ( is_array( $saved ) ) {
            foreach ( $defaults as $key => $default ) {
                if ( isset( $saved[ $key ] ) ) {
                    $defaults[ $key ] = min( 5000.0, max( 50.0, (float) $saved[ $key ] ) );
                }
            }
        }
        return $defaults;
    }

    protected function bottle_count( $ml, $bottle_ml ) {
        $bottle_ml = max( 1.0, (float) $bottle_ml );
        return (int) ceil( max( 0.0, (float) $ml ) / $bottle_ml );
    }

    protected function party_item( $label, $ml, $unit, $bottle_ml, $type ) {
        return array(
            'label'     => $label,
            'type'      => $type,
            'ml'        => $ml,
            'display'   => $this->ml_to_unit( $ml, $unit ),
            'bottle_ml' => $bottle_ml,
            'bottles'   => $this->bottle_count( $ml, $bottle_ml ),
        );
    }

    public function party( $args ) {
        $guests       = min( 500, max( 1, (int) ( $args['guests'] ?? 10 ) ) );
        $per_person   = min( 12.0, max( 0.1, (float) ( $args['drinks_per_person'] ?? 2 ) ) );
        $duration     = min( 24.0, max( 0.5, (float) ( $args['event_duration'] ?? 2 ) ) );
        $total_drinks = (int) ceil( $guests * $per_person );
        $unit         = $args['unit'] ?? 'ml';
        $result       = $this->batch( array_merge( $args, array( 'drinks' => $total_drinks ) ) );
        $presets      = $this->list_presets();
        $preset       = $presets[ $result['preset'] ] ?? $this->presets['classic'];
        $sizes        = $this->bottle_sizes();
        $region_key   = $this->normalise_standard_drink_region( $args['standard_drink_region'] ?? get_option( 'mm_standard_drink_region', 'AU' ) );
        $regions      = $this->standard_drink_regions();
        $std_grams    = $regions[ $region_key ]['grams'];
        $tequila_ml   = $result['quantities']['tequila']['ml'];
        $triple_ml    = $result['quantities']['triple']['ml'];
        $abv_overrides = $this->normalise_abv_overrides( $args, $preset );
        $alc_grams    = $this->calculate_alcohol_grams( $this->calculate_alcohol_ml( $result['quantities'], $abv_overrides ) );
        $shopping     = array(
            'spirits' => array(),
            'mixers'  => array(),
        );
        if ( $tequila_ml > 0 ) { $shopping['spirits'][] = $this->party_item( $result['quantities']['tequila']['label'] ?? __( 'Tequila', 'margarita-measurements' ), $tequila_ml, $unit, $sizes['tequila'], 'spirit' ); }
        if ( $triple_ml > 0 ) { $shopping['spirits'][] = $this->party_item( __( 'Triple sec', 'margarita-measurements' ), $triple_ml, $unit, $sizes['triple'], 'spirit' ); }
        $shopping['mixers'][] = $this->party_item( __( 'Citrus juice', 'margarita-measurements' ), $result['quantities']['citrus']['ml'], $unit, $sizes['citrus'], 'mixer' );
        if ( $result['quantities']['agave']['ml'] > 0 ) { $shopping['mixers'][] = $this->party_item( __( 'Agave syrup', 'margarita-measurements' ), $result['quantities']['agave']['ml'], $unit, $sizes['agave'], 'mixer' ); }
        if ( ! empty( $result['quantities']['flavour'] ) && $result['quantities']['flavour']['ml'] > 0 ) { $shopping['mixers'][] = $this->party_item( $result['quantities']['flavour']['label'], $result['quantities']['flavour']['ml'], $unit, $sizes['mixer'], 'mixer' ); }
        $result['mode'] = 'party';
        $result['guests'] = $guests;
        $result['drinks_per_person'] = $per_person;
        $result['event_duration'] = $duration;
        $result['total_margaritas'] = $total_drinks;
        $result['shopping_list'] = $shopping;
        $result['garnish_extras'] = array(
            'lime_wedges' => $total_drinks,
            'limes'       => (int) ceil( $total_drinks / 8 ),
            'salt'        => $this->salt_rim( $total_drinks, ! empty( $args['wet_rim'] ) ),
            'ice_kg'      => round( $total_drinks * (float) ( $preset['ice_factor'] ?? 1.0 ) * 0.18, 1 ),
        );
        $result['responsible_drinking'] = array(
            'region' => $region_key,
            'standard_drink_grams' => $std_grams,
            'estimated_standard_drinks' => round( $std_grams > 0 ? $alc_grams / $std_grams : 0, 1 ),
            'note' => sprintf( __( 'Plan water, food, transport and alcohol-free options. Estimates use %1$s standard drinks at %2$sg alcohol each; Australia defaults to 10g.', 'margarita-measurements' ), $region_key, $std_grams ),
        );
        return $this->add_nutrition( $result, $args, $abv_overrides );
    }

    public function batch( $args ) {
        $preset_key  = isset( $args['preset'] ) ? $args['preset'] : 'classic';
        $drinks      = max( 1, (int) ( $args['drinks'] ?? 1 ) );
        $unit        = $args['unit'] ?? 'ml';
        $wet_rim     = ! empty( $args['wet_rim'] );
        $flavour_key = $this->normalise_flavour_key( $args['flavour'] ?? 'none' );
        $presets     = $this->list_presets();
        $preset      = $presets[ $preset_key ] ?? $this->presets['classic'];
        $amounts     = array(
            'original_tequila_ml' => $preset['tequila_ml'] * $drinks,
            'original_triple_ml'  => $preset['triple_ml'] * $drinks,
            'tequila_ml'          => $preset['tequila_ml'] * $drinks,
            'citrus_ml'           => $preset['citrus_ml'] * $drinks,
            'triple_ml'           => $preset['triple_ml'] * $drinks,
            'agave_ml'            => $preset['agave_ml'] * $drinks,
        );
        $amounts     = $this->apply_flavour( $amounts, $flavour_key, $drinks );
        $ice_mul     = $preset['ice_factor'];
        $total_ml    = $amounts['tequila_ml'] + $amounts['citrus_ml'] + $amounts['triple_ml'] + $amounts['agave_ml'] + $amounts['extra_ml'];
        $meta          = $this->flavour_meta( $flavour_key );
        $abv_overrides = $this->normalise_abv_overrides( $args, $preset );
        $abv           = $meta['no_alcohol'] ? 0 : $this->abv_estimate( $amounts['tequila_ml'], $amounts['triple_ml'], $total_ml, $abv_overrides['triple_sec_abv'] / 100, $abv_overrides['tequila_abv'] / 100 );
        if ( ! empty( $amounts['extra'] ) ) {
            $amounts['extra']['display'] = $this->ml_to_unit( $amounts['extra']['ml'], $unit );
        }

        $result = array(
            'mode'       => 'drinks',
            'drinks'     => $drinks,
            'unit'       => $unit,
            'quantities' => array(
                'tequila'        => array( 'ml' => $amounts['tequila_ml'], 'display' => $this->ml_to_unit( $amounts['tequila_ml'], $unit ), 'label' => $meta['tequila_label'] ),
                'citrus'         => array( 'ml' => $amounts['citrus_ml'], 'display' => $this->ml_to_unit( $amounts['citrus_ml'], $unit ) ),
                'triple'         => array( 'ml' => $amounts['triple_ml'], 'display' => $this->ml_to_unit( $amounts['triple_ml'], $unit ) ),
                'agave'          => array( 'ml' => $amounts['agave_ml'], 'display' => $this->ml_to_unit( $amounts['agave_ml'], $unit ) ),
                'flavour'        => $amounts['extra'],
                'ice_multiplier' => $ice_mul,
                'total_ml'       => $total_ml,
            ),
            'salt_rim'   => $this->salt_rim( $drinks, $wet_rim ),
            'abv'        => $abv,
            'preset'     => $preset_key,
            'preset_label' => $preset['label'] ?? ucfirst( $preset_key ),
            'flavour'    => $meta,
            'suffix'     => $this->unit_suffix( $unit ),
            'abv_overrides' => $abv_overrides,
        );
        return $this->add_nutrition( $result, $args, $abv_overrides );
    }

    public function pitcher( $args ) {
        $pitcher_ml  = min( 5000.0, max( 100.0, (float) ( $args['pitcher_ml'] ?? 1000.0 ) ) );
        $preset_key  = $args['preset'] ?? 'classic';
        $presets     = $this->list_presets();
        $preset      = $presets[ $preset_key ] ?? $this->presets['classic'];
        $unit        = $args['unit'] ?? 'ml';
        $wet_rim     = ! empty( $args['wet_rim'] );
        $flavour_key = $this->normalise_flavour_key( $args['flavour'] ?? 'none' );
        $base_parts  = $preset['tequila_ml'] + $preset['citrus_ml'] + $preset['triple_ml'] + $preset['agave_ml'];
        if ( $base_parts <= 0 ) {
            $base_parts = 90.0;
        }
        $scale   = $pitcher_ml / $base_parts;
        $amounts = array(
            'original_tequila_ml' => $preset['tequila_ml'] * $scale,
            'original_triple_ml'  => $preset['triple_ml'] * $scale,
            'tequila_ml'          => $preset['tequila_ml'] * $scale,
            'citrus_ml'           => $preset['citrus_ml'] * $scale,
            'triple_ml'           => $preset['triple_ml'] * $scale,
            'agave_ml'            => $preset['agave_ml'] * $scale,
        );
        $amounts = $this->apply_flavour( $amounts, $flavour_key, $scale );
        $total_ml = $amounts['tequila_ml'] + $amounts['citrus_ml'] + $amounts['triple_ml'] + $amounts['agave_ml'] + $amounts['extra_ml'];
        $meta          = $this->flavour_meta( $flavour_key );
        $abv_overrides = $this->normalise_abv_overrides( $args, $preset );
        $abv           = $meta['no_alcohol'] ? 0 : $this->abv_estimate( $amounts['tequila_ml'], $amounts['triple_ml'], $total_ml, $abv_overrides['triple_sec_abv'] / 100, $abv_overrides['tequila_abv'] / 100 );
        $drinks   = max( 1, (int) round( $pitcher_ml / 90 ) );
        if ( ! empty( $amounts['extra'] ) ) {
            $amounts['extra']['display'] = $this->ml_to_unit( $amounts['extra']['ml'], $unit );
        }

        $result = array(
            'mode'       => 'pitcher',
            'pitcher_ml' => $pitcher_ml,
            'unit'       => $unit,
            'quantities' => array(
                'tequila' => array( 'ml' => $amounts['tequila_ml'], 'display' => $this->ml_to_unit( $amounts['tequila_ml'], $unit ), 'label' => $meta['tequila_label'] ),
                'citrus'  => array( 'ml' => $amounts['citrus_ml'], 'display' => $this->ml_to_unit( $amounts['citrus_ml'], $unit ) ),
                'triple'  => array( 'ml' => $amounts['triple_ml'], 'display' => $this->ml_to_unit( $amounts['triple_ml'], $unit ) ),
                'agave'   => array( 'ml' => $amounts['agave_ml'], 'display' => $this->ml_to_unit( $amounts['agave_ml'], $unit ) ),
                'flavour' => $amounts['extra'],
            ),
            'salt_rim'   => $this->salt_rim( $drinks, $wet_rim ),
            'abv'        => $abv,
            'preset'     => $preset_key,
            'preset_label' => $preset['label'] ?? ucfirst( $preset_key ),
            'flavour'    => $meta,
            'suffix'     => $this->unit_suffix( $unit ),
            'abv_overrides' => $abv_overrides,
        );
        return $this->add_nutrition( $result, $args, $abv_overrides );
    }

}
