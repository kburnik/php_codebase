<?php

namespace base\math\complex;

use base\math\complex\Complex;
use PHPUnit\Framework\TestCase;

class ComplexTest extends TestCase {
  public function testReadComponents() {
    $c = Complex::of(3, 7);

    $this->assertEquals(
        array(3, 7),
        array($c->re, $c->im));
  }

  public function testAdd_twoComplexNumbers_createsSum() {
    $this->assertEquals(
        Complex::of(4, 6),
        Complex::of(1, 2)->add(Complex::of(3, 4)));
  }

  public function testMultiply_twoComplexNumbers_createsProduct() {
    $this->assertEquals(
        Complex::of(-11, 29),
        Complex::of(2, 3)->multiply(Complex::of(5, 7)));
  }

  public function testToString() {
    $this->assertEquals(
        "2.35 + 4.72i",
        (string)Complex::of(2.35, 4.72));
  }
}

echo "From ComplexTest.php\n";
