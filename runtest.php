#!/usr/bin/env php
<?php

list($script, $outputfile) = $argv;
array_shift($argv);
array_shift($argv);
$testfiles = $argv;

$autoload = __DIR__ . "/autoload.php";

echo "Running test\n";
foreach($testfiles as $testfile) {
  ob_start();
  $DIR=__DIR__;
  system("$DIR/vendor/bin/phpunit --bootstrap $autoload $DIR/$testfile",
         $return_var);
  $output = ob_get_contents();
  file_put_contents($outputfile, $output);
  ob_end_clean();
  if ($return_var != 0) {
    echo $output;
    exit($return_var);
  }
}
