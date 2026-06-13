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

    public function batch( $args ) {
        $preset_key = isset( $args['preset'] ) ? $args['preset'] : 'classic';
        $drinks     = max( 1, (int) ( $args['drinks'] ?? 1 ) );
        $unit       = $args['unit'] ?? 'ml';
        $wet_rim    = ! empty( $args['wet_rim'] );
        $presets    = $this->list_presets();
        $preset     = $presets[ $preset_key ] ?? $this->presets['classic'];
        $teq_ml     = $preset['tequila_ml'] * $drinks;
        $citr_ml    = $preset['citrus_ml'] * $drinks;
        $trip_ml    = $preset['triple_ml'] * $drinks;
        $agav_ml    = $preset['agave_ml'] * $drinks;
        $ice_mul    = $preset['ice_factor'];
        $total_ml   = $teq_ml + $citr_ml + $trip_ml + $agav_ml;
        $abv        = $this->abv_estimate( $teq_ml, $trip_ml, $total_ml, $preset['triple_abv'] ?? 0.40 );

        return array(
            'mode'       => 'drinks',
            'drinks'     => $drinks,
            'unit'       => $unit,
            'quantities' => array(
                'tequila'        => array( 'ml' => $teq_ml, 'display' => $this->ml_to_unit( $teq_ml, $unit ) ),
                'citrus'         => array( 'ml' => $citr_ml, 'display' => $this->ml_to_unit( $citr_ml, $unit ) ),
                'triple'         => array( 'ml' => $trip_ml, 'display' => $this->ml_to_unit( $trip_ml, $unit ) ),
                'agave'          => array( 'ml' => $agav_ml, 'display' => $this->ml_to_unit( $agav_ml, $unit ) ),
                'ice_multiplier' => $ice_mul,
                'total_ml'       => $total_ml,
            ),
            'salt_rim'   => $this->salt_rim( $drinks, $wet_rim ),
            'abv'        => $abv,
            'preset'     => $preset_key,
            'suffix'     => $this->unit_suffix( $unit ),
        );
    }

    public function pitcher( $args ) {
        $pitcher_ml = min( 5000.0, max( 100.0, (float) ( $args['pitcher_ml'] ?? 1000.0 ) ) );
        $preset_key = $args['preset'] ?? 'classic';
        $presets    = $this->list_presets();
        $preset     = $presets[ $preset_key ] ?? $this->presets['classic'];
        $unit       = $args['unit'] ?? 'ml';
        $wet_rim    = ! empty( $args['wet_rim'] );
        $total_parts = $preset['tequila_ml'] + $preset['citrus_ml'] + $preset['triple_ml'] + $preset['agave_ml'];
        if ( $total_parts <= 0 ) {
            $total_parts = 90.0;
        }
        $scale   = $pitcher_ml / $total_parts;
        $teq_ml  = $preset['tequila_ml'] * $scale;
        $citr_ml = $preset['citrus_ml'] * $scale;
        $trip_ml = $preset['triple_ml'] * $scale;
        $agav_ml = $preset['agave_ml'] * $scale;
        $abv     = $this->abv_estimate( $teq_ml, $trip_ml, $pitcher_ml, $preset['triple_abv'] ?? 0.40 );
        $drinks  = max( 1, (int) round( $pitcher_ml / 90 ) );

        return array(
            'mode'       => 'pitcher',
            'pitcher_ml' => $pitcher_ml,
            'unit'       => $unit,
            'quantities' => array(
                'tequila' => array( 'ml' => $teq_ml, 'display' => $this->ml_to_unit( $teq_ml, $unit ) ),
                'citrus'  => array( 'ml' => $citr_ml, 'display' => $this->ml_to_unit( $citr_ml, $unit ) ),
                'triple'  => array( 'ml' => $trip_ml, 'display' => $this->ml_to_unit( $trip_ml, $unit ) ),
                'agave'   => array( 'ml' => $agav_ml, 'display' => $this->ml_to_unit( $agav_ml, $unit ) ),
            ),
            'salt_rim'   => $this->salt_rim( $drinks, $wet_rim ),
            'abv'        => $abv,
            'preset'     => $preset_key,
            'suffix'     => $this->unit_suffix( $unit ),
        );
    }
}
