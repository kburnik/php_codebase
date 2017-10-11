<?php

namespace tools\build;

use \Exception;

/** Generic parsed command line options. */
class Options {
  private $options;

  /** Parses the array of arguments, without the script name. */
  public static function parse($arguments) {
    $arguments[] = "--";
    $opts = array();
    $state = "";
    foreach ($arguments as $arg) {
      if (substr($arg, 0, 2) == "--") {
        $state = substr($arg, 2);

        if ($state != "" && !array_key_exists($state, $opts)) {
          $opts[$state] = array();
        }
      } else if ($state != "") {
        $opts[$state][] = $arg;
      }
    }
    return new Options($opts);
  }

  private function __construct($options) {
    $this->options = $options;
  }

  public function get_string($name) {
    if (!array_key_exists($name, $this->options) ||
      count($this->options[$name]) == 0 ||
      strlen($this->options[$name][0]) == 0) {
      throw new Exception("Missing requested option $name");
    }
    return $this->options[$name][0];
  }

  public function get_list($name, $must_have_elements=false) {
    if (!array_key_exists($name, $this->options) ||
      $must_have_elements && count($this->options[$name]) == 0) {
      throw new Exception("Missing requested option $name");
    }
    return $this->options[$name];
  }

  public function has($name) {
    return array_key_exists($name, $this->options);
  }

  public function show() {
    print_r($this->options);
  }
}
