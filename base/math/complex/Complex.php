<?php

namespace base\math\complex;

use base\type\Immutable;

/** A complex number representation. */
class Complex extends Immutable {
  // @override
  protected function __construct($re = 0.0, $im = 0.0) {
    $this->re = $re;
    $this->im = $im;
    parent::__construct();
  }

  public static function of($re = 0.0, $im = 0.0) {
    return new Complex($re, $im);
  }

  public function add($that) {
    return Complex::of(
        $this->re + $that->re,
        $this->im + $that->im);
  }

  public function multiply($that) {
    // (a + bi) * (c + di) = (ac - bd) + (ad + bc)i
    list($a, $b, $c, $d) = array($this->re, $this->im, $that->re, $that->im);
    return Complex::of($a * $c - $b * $d, $a * $d + $b * $c);
  }

  public function __toString() {
    return "{$this->re} + {$this->im}i";
  }
}
