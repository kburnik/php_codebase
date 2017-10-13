#!/usr/bin/env php
<?php
# Generated autoloader for {target}.

define("{target_app_root}", realpath(__DIR__ . "/{relpath}"));

# TODO(kburnik): Consider making this static from list of external deps.
foreach(glob({target_app_root} . "/external/*/autoload.php") as $external) {
  require_once($external);
}

spl_autoload_register(function($class) {
  static $dependencyWhitelist = {deps};
  $classFilePath = str_replace("\\", "/", $class) . ".php";
  $isWhiteListed = array_key_exists($classFilePath, $dependencyWhitelist);
  $isOnFileSystem = file_exists({target_app_root} . "/" . $classFilePath);

  if (!$isWhiteListed && $isOnFileSystem) {
    throw new Exception(
        "Attempted to load $classFilePath which is not listed as a " .
        "dependency to the {namespace}\{class}. Have you included it to the " .
        "target's dependencies?");
  } else if (!$isWhiteListed && !$isOnFileSystem) {
    return false;
  } else if ($isWhiteListed && !$isOnFileSystem) {
    throw new Exception(
        "File $classFilePath could not be found, but was listed as a " .
        "dependency. Try rebuilding the target and running it again.");
  } else {
    require_once({target_app_root} . "/" . $classFilePath);
    return true;
  }
});
