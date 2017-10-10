<?php

namespace app\calc;

use base\math\complex\Complex;

class Calc {
  public static function main($args) {
    $a = Complex::of($args[0], $args[1]);
    $op = $args[2];
    $b = Complex::of($args[3], $args[4]);

    switch($op) {
      case "+":
        $r = $a->add($b);
        break;
      case "*":
        $r = $a->multiply($b);
        break;
      default:
        throw new Exception("Invalid op: $op");
    }

    echo "$r\n";
    return 0;
  }
}
