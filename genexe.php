#!/usr/bin/env php
<?php

$out = $argv[1];
$script = $argv[2];

$path_parts = explode("/", $script);
$class = str_replace(".php", "", array_pop($path_parts));
$path_len = count($path_parts);
$namespace = implode("\\", $path_parts);

$relpath = implode("/", array_fill(0, $path_len, ".."));

$template = '#!/usr/bin/env php
<?php
# Generated entry point for {script}.

define("__APP_ROOT__", realpath(__DIR__ . "/{relpath}"));

spl_autoload_register(function($class) {
  $classFilePath =
      __APP_ROOT__ . "/" . str_replace("\\\", "/", $class) . ".php";
  $exists = file_exists($classFilePath);
  if ($exists) {
    require_once($classFilePath);
  }
  return $exists;
});

require_once(__APP_ROOT__ . "/vendor/autoload.php");
require_once(__APP_ROOT__ . "/{script}");

array_shift($argv);
exit(intval(\\{namespace}\\{class}::main($argv)));
';

$generated = strtr($template, array('{script}' => $script,
                                    '{namespace}' => $namespace,
                                    '{class}' => $class,
                                    '{relpath}' => $relpath));

file_put_contents($out, $generated);
