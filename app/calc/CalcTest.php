<?php

namespace app\calc;

use app\calc\App;
use PHPUnit\Framework\TestCase;

class CalcTest extends TestCase {

  public function testCanAddNumbers() {
    $this->assertEquals("4 + 6i", $this->calc(1, 2, '+', 3, 4));
  }

  private function calc() {
    $inputs = func_get_args();
    ob_start();
    Calc::main($inputs);
    $res = trim(ob_get_contents());
    ob_end_clean();
    return $res;
  }
}
