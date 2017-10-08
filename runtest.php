#!/usr/bin/env php
<?php

list($script, $outputfile) = $argv;
array_shift($argv);
array_shift($argv);
$testfiles = $argv;

$autoload = __DIR__ . "/autoload.php";

foreach($testfiles as $testfile) {
  ob_start();
  $DIR=__DIR__;
  $flags="--report-useless-tests --testdox";
  system("$DIR/vendor/bin/phpunit $flags --bootstrap $autoload $DIR/$testfile",
         $return_var);
  $output = ob_get_contents();
  file_put_contents($outputfile, $output, FILE_APPEND);
  ob_end_clean();
  if ($return_var != 0) {
    echo $output;
    exit($return_var);
  }
}
