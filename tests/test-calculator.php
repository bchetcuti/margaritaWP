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
}
