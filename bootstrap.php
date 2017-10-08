#!/usr/bin/env php
<?php

# Bootstraps a PHP library - sets up autoloading and includes files.

# TODO(burnik): Try running srcs from blaze-bin instead of the src dir.

require_once(__DIR__ . "/vendor/autoload.php");

$output = $argv[1];
array_shift($argv);
array_shift($argv);

$elements = array("deps" => array(), "srcs" => array());
$all_sources = array();
$argv[] = "--sentinel";
$state = "";
foreach($argv as $arg) {
  if (substr($arg, 0, 2) == "--") {
    $state = substr($arg, 2);
  } else {
    $elements[$state][] = $arg;
    $all_sources[] = $arg;
  }
}

if (in_array("--showdeps", $argv)) {
  print_r($elements);
}

// Loader which looks at deps given via args.
spl_autoload_register(function($class) {
  global $all_sources;
  $classFilePath = str_replace("\\", "/", $class) . ".php";

  $exists = in_array($classFilePath, $all_sources);

  if ($exists) {
    require_once(__DIR__ . "/". $classFilePath);
  }

  return $exists;
});

if (count($elements['srcs']) == 0) {
  throw new Exception("No source files provided");
}

foreach ($elements['srcs'] as $script) {
  ob_start();
  require_once(__DIR__ . "/" . $script);
  file_put_contents($output, ob_get_contents());
  ob_end_clean();
  if (filesize($output) > 0) {
    echo "-- output $script --\n";
    echo file_get_contents($output);
    echo "\n-- /output --\n";
    throw new Exception("Executed script should not output!");
  }
}
