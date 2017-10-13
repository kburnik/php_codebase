<?php

namespace app\calc;

use base\math\complex\Complex;
use \Exception;

class Calc {
  public static function main($args) {
    $expression = $args[0];

    $decimal = '[+-]?[0-9]{1,3}(?:,?[0-9]{3})*(?:\.[0-9]+)?';
    $complex = "({$decimal})\s*\+{0,1}\s*({$decimal}|)i";
    $ok = preg_match("/{$complex}\s([\+\*])\s${complex}/",
                     $expression,
                     $parsed);
    if (!$ok) {
      echo "Invalid expression: $expression\n";
      return 1;
    }

    array_shift($parsed);
    list($a, $b, $op, $c, $d) = $parsed;
    if ($b == "") $b = 1;
    if ($d == "") $d = 1;

    $first = Complex::of($a, $b);
    $second = Complex::of($c, $d);

    switch($op) {
      case "+":
        $r = $first->add($second);
        break;
      case "*":
        $r = $first->multiply($second);
        break;
      default:
        throw new Exception("Invalid op: $op");
    }

    echo "$r\n";
    return 0;
  }
}
