#!/usr/bin/env php
<?php

# Generates an executable script for running tests.

list($script, $outputfile) = $argv;
array_shift($argv);
array_shift($argv);
$testfiles = $argv;

$DIR = ".";
$autoload = "$DIR/autoload.php";

echo "Generating test\n";
foreach($testfiles as $testfile) {
  $flags="--report-useless-tests";
  file_put_contents(
      $outputfile,
      "echo 'Testing: $testfile'\n" .
      "$DIR/vendor/bin/phpunit $flags --bootstrap $autoload $DIR/$testfile\n",
      FILE_APPEND);
}
