#!/usr/bin/env php
<?php

require_once(__DIR__ . "/vendor/autoload.php");

$deps_map = null;
$target = null;

spl_autoload_register(function($class) {
  global $deps_map;
  global $target;

  $classFilePath = str_replace("\\", "/", $class) . ".php";

  $exists = $deps_map === null &&
            file_exists(__DIR__ . "/" . $classFilePath) ||
            array_key_exists($classFilePath, $deps_map);

  if ($exists) {
    require_once(__DIR__ . "/" . $classFilePath);
  } else if ($deps_map !== null) {
    throw new Exception(
        "Dependency to {$classFilePath} missing for target {$target}.");
  }

  return $exists;
});

if (count($argv) > 1 && basename($argv[0]) == basename(__FILE__)) {
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

  $opts = parse_args($argv);
  $deps_map = array_flip($opts['dep']);
  $target = $opts['target'][0];
  foreach ($opts['src'] as $src) {
    try {
      echo " ... Loading $src\n";
      require_once($src);
    } catch(Exception $ex) {
      $error = $ex->getMessage();
      echo "\n--> $error (context: $src) \n\n";
      exit(1);
    }
  }
}
