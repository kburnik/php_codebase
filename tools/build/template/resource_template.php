<?php

# Generated static resource class.

namespace {namespace};

use \Exception;

class StaticResource {
  private static $data = {data};
  private $filename;

  private function __construct($filename) {
    $this->filename = $filename;
  }

  public function read() {
    return file_get_contents(__DIR__ . "/" . $this->filename);
  }

  public static function find($pattern) {
    $results = array();
    foreach (self::$data as $filename) {
      if (fnmatch($pattern, $filename)) {
        $results[] = new StaticResource($filename);
      }
    }
    return $results;
  }

  public static function readFile($filename) {
    $matches = self::find($filename);
    if (count($matches) != 1) {
      throw new Exception("Could not match single file: $filename");
    }
    return $matches[0]->read();
  }
}
