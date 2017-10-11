<?php

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


