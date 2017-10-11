<?php

namespace app\calc;

use PHPUnit\Framework\TestCase;

class CalcTest extends TestCase {
  public function testCanAddNumbers() {
    $this->assertEquals("4 + 6i", $this->runCalc("1+2i + 3+4i"));
  }

  public function testCanMultiplyNumbers() {
    $this->assertEquals("-5 + 10i", $this->runCalc("1+2i * 3+4i"));
  }

  private function runCalc() {
    $inputs = func_get_args();
    ob_start();
    Calc::main($inputs);
    $res = trim(ob_get_contents());
    ob_end_clean();
    return $res;
  }
}
