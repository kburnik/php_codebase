#!/usr/bin/env php
<?php

list($script, $outputfile) = $argv;
array_shift($argv);
array_shift($argv);
$testfiles = $argv;

$autoload = __DIR__ . "/autoload.php";

foreach($testfiles as $testfile) {
  $DIR=__DIR__;
  $flags="--report-useless-tests --testdox";
  file_put_contents(
      $outputfile,
      "echo 'Testing: $testfile'\n" .
      "$DIR/vendor/bin/phpunit $flags --bootstrap $autoload $DIR/$testfile\n",
      FILE_APPEND);
}
