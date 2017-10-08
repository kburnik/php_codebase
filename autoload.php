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

