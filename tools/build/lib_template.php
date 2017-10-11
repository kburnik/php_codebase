<?php
# Bootstrapping of library {target}.

$srcs = {srcs};
foreach ($srcs as $src) {
  try {
    echo " ... Loading $src\n";
    require_once($src);
  } catch(Exception $ex) {
    $error = $ex->getMessage();
    echo "\n--> $error (context: $src) \n\n";
    exit(1);
  }
}
