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
}
