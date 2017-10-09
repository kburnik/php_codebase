<?php

require_once(__DIR__ . "/vendor/autoload.php");

spl_autoload_register(function($class) {
  $classFilePath =
      __DIR__ . "/" .
      str_replace("\\", "/", $class) . ".php";

  $exists = file_exists($classFilePath);

  if ($exists) {
    require_once($classFilePath);
  }

  return $exists;
});

if (count($argv) > 1 && basename($argv[0]) == basename(__FILE__)) {
  array_shift($argv);
  foreach ($argv as $arg) {
    echo "Loading $arg\n";
    require_once($arg);
  }
}
