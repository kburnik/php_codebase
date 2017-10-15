#!/usr/bin/env php
<?php

namespace tools\build;

# Library builder script.
require_once(__DIR__ . '/Builder.php');
require_once(__DIR__ . '/Options.php');

class Build {
  public static function main($args) {
    $opts = Options::parse($args);
    if ($opts->has('verbose')) {
       $opts->show();
    }

    $type = $opts->get_string('type');
    $out_dir = $opts->get_string('out');
    $srcs = $opts->get_list('src', /*must_have_elements=*/true);
    $deps = $opts->get_list('dep');
    $target = $opts->get_string('target');

    Builder::build($type, $out_dir, $srcs, $deps, $target);
  }
}

Build::main(array_slice($argv, 1));
