<?php

namespace app\story;

use app\story\data\StaticResource as Data;
use PHPUnit\Framework\TestCase;

class StoryTest extends TestCase {
  public function testCanReadStory() {
    $this->assertEquals(Data::readFile("story-1.txt"), $this->runStory(1));
  }

  public function testShowsErrorOnMissingIndex() {
    $this->assertEquals("Could not find story with index: 100\n",
                        $this->runStory(100));
  }

  private function runStory() {
    ob_start();
    Story::main(func_get_args());
    $res = ob_get_contents();
    ob_end_clean();
    return $res;
  }
}
