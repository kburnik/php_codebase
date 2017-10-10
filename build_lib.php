#!/usr/bin/env php
<?php

# --out $directory --src $src... --dep $dep... [--bootstrap]

function parse_args($arguments) {
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

  return $opts;
}

// Join path.
function jp() {
  return implode("/", func_get_args());
}

function place_file($filename, $out_dir) {
  $dest_file = jp($out_dir, $filename);
  $dest_dir = dirname($dest_file);
  if (!file_exists($dest_dir)) {
    mkdir($dest_dir, 0755, true);
  }
  echo "Placing file: $filename\n";
  copy($filename, $dest_file);
}

function run_cmd($cmd, $error="Error running cmd") {
  system("$cmd", $retval);
  if ($retval != 0) {
    throw new Exception($error);
  }
}

function check_syntax($filename) {
  echo "Checking syntax: $filename\n";
  run_cmd("php -l $filename > /dev/null", "Invalid syntax in $filename");
}

function check_file_exists($filename) {
  if (!file_exists($filename)) {
    throw new Exception("Required file does not exist: $filename");
  }
}

function bootstrap($autoload, $srcs, $deps, $target) {
  check_file_exists($autoload);
  run_cmd(
    "php $autoload " .
      "--src " . implode(" ", $srcs) . " " .
      "--dep " . implode(" ", $deps) . " " .
      "--target {$target}",
    "Failed bootstraping target {$target}.");
}

class Options {
  private $options;

  public function __construct($options) {
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

function main($arguments) {
  $opts = new Options(parse_args($arguments));

  if ($opts->has('verbose')) {
     $opts->show();
  }

  $out_dir = $opts->get_string('out');
  $srcs = $opts->get_list('src', /*must_have_elements=*/true);
  $deps = $opts->get_list('dep');
  $target = $opts->get_string('target');

  $php_files = array();
  foreach ($srcs as $src) {
    if (strtolower(substr($src, -4)) == ".php") {
      check_syntax($src);
      $php_files[] = $src;
    }
    place_file($src, $out_dir);
  }

  if ($opts->has('bootstrap')) {
    bootstrap("$out_dir/autoload.php", $php_files, $deps, $target);
    echo "Successfully bootstrapped target {$target}.\n";
  }
}

try {
  main($argv);
} catch (Exception $ex) {
  echo "--> {$ex->getMessage()}\n";
  exit(1);
}
