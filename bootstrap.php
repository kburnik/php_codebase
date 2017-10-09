#!/usr/bin/env php
<?php
# Bootstraps a PHP library - sets up autoloading and includes all dependant
# source files. This ensures the code is valid and can actually execute.

require_once(__DIR__ . "/vendor/autoload.php");

$output = $argv[1];
array_shift($argv);
array_shift($argv);

$DIR = __DIR__ . "/bazel-bin";
$autoload = "$DIR/autoload.php";
$srcs = array();
foreach ($argv as $src) {
  $srcs[] = "$DIR/$src";
}
$srcs = implode(" ", $srcs);

$bootstrap_script = "#!/bin/bash\necho $output\nphp $autoload $srcs\n";
file_put_contents($output, $bootstrap_script);
chmod($output, 0755);
echo "Running bootstrapped target $output\n";
system($output, $retval);
exit($retval);
