<?php
class MM_Calculator {
    protected $presets = array(
        'classic' => array(
            'tequila_ml'   => 45.0,
            'citrus_ml'    => 30.0,
            'triple_ml'    => 15.0,
            'agave_ml'     => 0.0,
            'ice_factor'   => 1.0,
            'note'         => 'Classic 3:2:1',
        ),
        'tommys' => array(
            'tequila_ml'   => 60.0,
            'citrus_ml'    => 30.0,
            'triple_ml'    => 0.0,
            'agave_ml'     => 7.5,
            'ice_factor'   => 1.0,
            'note'         => "Tommy's (no triple sec)",
        ),
        'frozen' => array(
            'tequila_ml'   => 50.0,
            'citrus_ml'    => 30.0,
            'triple_ml'    => 20.0,
            'agave_ml'     => 0.0,
            'ice_factor'   => 1.5,
            'note'         => 'Frozen (blended)',
        ),
        'skinny' => array(
            'tequila_ml'   => 50.0,
            'citrus_ml'    => 35.0,
            'triple_ml'    => 7.5,
            'agave_ml'     => 0.0,
            'ice_factor'   => 1.0,
            'note'         => 'Skinny (lighter liqueur)',
        ),
    );

    public function list_presets() {
        return $this->presets;
    }

    public function ml_to_unit( $ml, $unit ) {
        switch ( $unit ) {
            case 'oz':   return $ml / 29.5735;
            case 'shot': return $ml / 44.0;
            case 'nip':  return $ml / 30.0;
            case 'ml':
            default:     return $ml;
        }
    }

    public function unit_suffix( $unit ) {
        switch ( $unit ) {
            case 'oz':   return 'oz';
            case 'shot': return 'shot(s)';
            case 'nip':  return 'nip(s)';
            case 'ml':
            default:     return 'ml';
        }
    }

    public function abv_estimate( $tequila_ml, $triple_ml, $total_ml ) {
        $alc_ml = ( $tequila_ml * 0.40 ) + ( $triple_ml * 0.40 );
        if ( $total_ml <= 0 ) return 0;
        return ( $alc_ml / $total_ml ) * 100.0;
    }

    public function batch( $args ) {
        $preset_key = isset( $args['preset'] ) ? $args['preset'] : 'classic';
        $drinks     = max( 1, (int) ( $args['drinks'] ?? 1 ) );
        $unit       = $args['unit'] ?? 'ml';

        $preset = $this->presets[ $preset_key ] ?? $this->presets['classic'];

        $teq_ml   = $preset['tequila_ml'] * $drinks;
        $citr_ml  = $preset['citrus_ml']  * $drinks;
        $trip_ml  = $preset['triple_ml']  * $drinks;
        $agav_ml  = $preset['agave_ml']   * $drinks;
        $ice_mul  = $preset['ice_factor'];

        $total_ml = $teq_ml + $citr_ml + $trip_ml + $agav_ml;
        $abv = $this->abv_estimate( $teq_ml, $trip_ml, $total_ml );

        $result = array(
            'drinks'   => $drinks,
            'unit'     => $unit,
            'quantities' => array(
                'tequila' => array( 'ml' => $teq_ml, 'display' => $this->ml_to_unit( $teq_ml, $unit ) ),
                'citrus'  => array( 'ml' => $citr_ml, 'display' => $this->ml_to_unit( $citr_ml, $unit ) ),
                'triple'  => array( 'ml' => $trip_ml, 'display' => $this->ml_to_unit( $trip_ml, $unit ) ),
                'agave'   => array( 'ml' => $agav_ml, 'display' => $this->ml_to_unit( $agav_ml, $unit ) ),
                'ice_multiplier' => $ice_mul,
                'total_ml' => $total_ml,
            ),
            'abv'      => $abv,
            'preset'   => $preset_key,
            'suffix'   => $this->unit_suffix( $unit ),
        );

        return $result;
    }
}
