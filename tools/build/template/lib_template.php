<?php
# Generated bootstrapper for library {target}.

$srcs = {srcs};
foreach ($srcs as $src) {
  try {
    require_once($src);
  } catch(Exception $ex) {
    $error = $ex->getMessage();
    echo "\n--> $error (context: $src) \n\n";
    exit(1);
  }
}
