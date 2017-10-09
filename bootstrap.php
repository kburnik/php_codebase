#!/usr/bin/env php
<?php
# Bootstraps a PHP library - sets up autoloading and includes all dependant
# source files. This ensures the code is valid and can actually execute.

# TODO(burnik): Try running srcs from blaze-bin instead of the src dir
# This would ensure we have executable copies of the files, for example
# when we want to run tests while we have changed files which were not rebuilt.

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
  throw new Exception("No source files provided.");
}

# TODO(kburnik): These should be direct sources only. We want builds to fail
# (even for  test targets) if scripts can't get executed, i.e. find all deps.
foreach ($all_sources as $script) {
  if (in_array("verbose", $argv)) {
     echo " * Bootstrapping $src\n";
  }
  ob_start();
  file_put_contents($output, ob_get_contents(), FILE_APPEND);
  require_once(__DIR__ . "/" . $script);
  ob_end_clean();
  if (filesize($output) > 0) {
    echo "-- output $script --\n";
    echo file_get_contents($output);
    echo "\n-- /output --\n";
    throw new Exception("Library script should not output.");
  }
}
