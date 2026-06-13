<?php
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase {
    public function test_batch_basic() {
        $c = new MM_Calculator();
        $out = $c->batch([ 'preset' => 'classic', 'drinks' => 2, 'unit' => 'ml' ]);
        $this->assertEquals(2, $out['drinks']);
        $this->assertArrayHasKey('quantities', $out);
        $this->assertGreaterThan(0, $out['quantities']['tequila']['ml']);
    }

    public function test_mango_adds_nectar_and_dilutes_abv() {
        $c = new MM_Calculator();
        $plain = $c->batch([ 'preset' => 'classic', 'drinks' => 1, 'unit' => 'ml' ]);
        $mango = $c->batch([ 'preset' => 'classic', 'drinks' => 1, 'unit' => 'ml', 'flavour' => 'mango' ]);
        $this->assertEquals('mango', $mango['flavour']['key']);
        $this->assertEquals(30.0, $mango['quantities']['flavour']['ml']);
        $this->assertLessThan($plain['abv'], $mango['abv']);
    }

    public function test_virgin_removes_alcohol() {
        $c = new MM_Calculator();
        $out = $c->batch([ 'preset' => 'classic', 'drinks' => 1, 'unit' => 'ml', 'flavour' => 'virgin' ]);
        $this->assertEquals(0.0, $out['quantities']['tequila']['ml']);
        $this->assertEquals(0.0, $out['quantities']['triple']['ml']);
        $this->assertEquals(0, $out['abv']);
        $this->assertTrue($out['flavour']['no_alcohol']);
    }

    public function test_invalid_flavour_falls_back_to_none() {
        $c = new MM_Calculator();
        $this->assertEquals('none', $c->normalise_flavour_key('bad-flavour'));
    }

    public function test_standard_drink_region_normalises_configured_defaults() {
        $c = new MM_Calculator();
        $this->assertEquals('US', $c->normalise_standard_drink_region('US'));
        $this->assertEquals('UK', $c->normalise_standard_drink_region('uk'));
        $this->assertEquals('AU', $c->normalise_standard_drink_region('invalid'));
    }
    public function test_party_planning_outputs_shopping_list_and_au_standard_drinks() {
        $c = new MM_Calculator();
        $out = $c->party([ 'preset' => 'classic', 'guests' => 10, 'drinks_per_person' => 2, 'event_duration' => 3, 'unit' => 'ml', 'wet_rim' => true ]);
        $this->assertEquals('party', $out['mode']);
        $this->assertEquals(20, $out['total_margaritas']);
        $this->assertEquals(10, $out['guests']);
        $this->assertEquals(2.0, $out['drinks_per_person']);
        $this->assertNotEmpty($out['shopping_list']['spirits']);
        $this->assertNotEmpty($out['shopping_list']['mixers']);
        $this->assertEquals(10.0, $out['responsible_drinking']['standard_drink_grams']);
        $this->assertEquals('AU', $out['responsible_drinking']['region']);
        $this->assertGreaterThan(0, $out['garnish_extras']['limes']);
    }
}
