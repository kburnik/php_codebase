#!/usr/bin/env php
<?php

$out = $argv[1];
$script = $argv[2];
array_shift($argv);
array_shift($argv);
array_shift($argv);

$deps = $argv;
$path_parts = explode("/", $script);
$class = str_replace(".php", "", array_pop($path_parts));
$path_len = count($path_parts);
$namespace = implode("\\", $path_parts);

$relpath = implode("/", array_fill(0, $path_len, ".."));

$template = file_get_contents(__DIR__ . "/autoload_template.php");

$generated =
    strtr($template, array('{script}' => $script,
                           '{namespace}' => $namespace,
                           '{class}' => $class,
                           '{relpath}' => $relpath,
                           '{deps}' => var_export(array_flip($deps), true)));

file_put_contents($out, $generated);
