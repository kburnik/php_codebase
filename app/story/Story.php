<?php

namespace app\story;

use app\story\data\StaticResource as StoryData;

class Story {
  public static function main($args) {
    $index = reset($args);
    $matches = StoryData::find("story-{$index}.txt");
    if (count($matches)) {
      echo $matches[0]->read();
    } else {
      echo "Could not find story with index: $index\n";
      return 1;
    }
  }
}
