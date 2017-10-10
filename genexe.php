#!/usr/bin/env php
<?php

$out = $argv[1];
$script = $argv[2];

$namespace = explode("/", $script);
$class = str_replace(array_pop($namespace), ".php", "");
$namespace = implode("\\", $namespace);

$template = '#!/usr/bin/env php
<?php
# Generated entry point for {script}.

spl_autoload_register(function($class) {
  $classFilePath = str_replace("\\\", "/", $class) . ".php";
  $exists = file_exists($classFilePath);
  if ($exists) {
    require_once($classFilePath);
  }
  return $exists;
});

require_once("vendor/autoload.php");
require_once("{script}");

array_shift($argv);
\\{namespace}\\{class}::main($argv);
';

$generated = strtr($template, array('{script}' => $script,
                                    '{namespace}' => $namespace,
                                    '{class}' => $class));

file_put_contents($out, $generated);
