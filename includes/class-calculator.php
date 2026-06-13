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

    public function abv_estimate( $tequila_ml, $triple_ml, $total_ml, $triple_abv = 0.40 ) {
        $alc_ml = ( $tequila_ml * 0.40 ) + ( $triple_ml * $triple_abv );
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
        $meta        = $this->flavour_meta( $flavour_key );
        $abv         = $meta['no_alcohol'] ? 0 : $this->abv_estimate( $amounts['tequila_ml'], $amounts['triple_ml'], $total_ml, $preset['triple_abv'] ?? 0.40 );
        if ( ! empty( $amounts['extra'] ) ) {
            $amounts['extra']['display'] = $this->ml_to_unit( $amounts['extra']['ml'], $unit );
        }

        return array(
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
        );
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
        $meta     = $this->flavour_meta( $flavour_key );
        $abv      = $meta['no_alcohol'] ? 0 : $this->abv_estimate( $amounts['tequila_ml'], $amounts['triple_ml'], $total_ml, $preset['triple_abv'] ?? 0.40 );
        $drinks   = max( 1, (int) round( $pitcher_ml / 90 ) );
        if ( ! empty( $amounts['extra'] ) ) {
            $amounts['extra']['display'] = $this->ml_to_unit( $amounts['extra']['ml'], $unit );
        }

        return array(
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
        );
    }

}
