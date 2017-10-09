#!/usr/bin/env php
<?php

list($script, $outputfile) = $argv;
array_shift($argv);
array_shift($argv);
$testfiles = $argv;

$DIR = __DIR__ . "/bazel-bin";
$autoload = "$DIR/autoload.php";

foreach($testfiles as $testfile) {
  $flags="--report-useless-tests";
  file_put_contents(
      $outputfile,
      "echo 'Testing: $testfile' in \$(pwd)\n" .
      "echo $DIR/vendor/bin/phpunit $flags --bootstrap $autoload $DIR/$testfile\n" .
      "$DIR/vendor/bin/phpunit $flags --bootstrap $autoload $DIR/$testfile\n",
      FILE_APPEND);
}
