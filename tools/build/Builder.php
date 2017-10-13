<?php

namespace tools\build;

use \Exception;

/** PHP target buider (library, binary or test). */
class Builder {

  /** Builds a PHP target. */
  public static function build(
      $type, $out_dir, $srcs, $deps, $target) {
    if ($type == "resource") {
      foreach ($srcs as $src) {
        self::placeFile($src, $out_dir);
      }
      self::create_resource($out_dir, $srcs);
    } else {
      $php_files = array();

      foreach ($srcs as $src) {
        if (strtolower(substr($src, -4)) == ".php") {
          self::checkSyntax($src);
          $php_files[] = $src;
        }
        self::placeFile($src, $out_dir);
      }

      self::bootstrap($type, $out_dir, $php_files, $deps, $target);
    }
  }

  public static function create_resource($out_dir, $data) {
    $path_parts = explode("/", $data[0]);
    array_pop($path_parts);
    $target_dir = implode("/", $path_parts);
    $namespace = implode("\\", $path_parts);
    $root_path = implode("/", $path_parts);

    $files = array();
    foreach ($data as $path) {
      if (substr($path, 0, strlen($root_path)) == $root_path) {
        $path = substr($path, strlen($root_path) + 1);
      }
      $files[] = $path;
    }

    $vars = array('{namespace}' => $namespace,
                  '{data}' => var_export($files, true));

    $out_file = "${out_dir}/{$target_dir}/StaticResource.php";
    self::concat($out_file,
                 array("resource_template.php"),
                 $vars);
  }

  private static function bootstrap($type, $out_dir, $srcs, $deps, $target) {
    $path_parts = explode("/", $srcs[0]);
    $class = str_replace(".php", "", array_pop($path_parts));
    $target_dir = implode("/", $path_parts);
    $path_len = count($path_parts);
    $namespace = implode("\\", $path_parts);
    $target_app_root =
        strtoupper("APP_ROOT_" . implode("_", $path_parts) . "_{$target}");
    $relpath = implode("/", array_fill(0, $path_len, ".."));

    $out_file = "${out_dir}/{$target_dir}/{$target}.bootstrap.php";
    $all_deps = array_merge($deps, $srcs);
    $vars = array('{srcs}' => var_export($srcs, true),
                  '{deps}' => var_export(array_flip($all_deps), true),
                  '{relpath}' => $relpath,
                  '{namespace}' => $namespace,
                  '{class}' => $class,
                  '{target}' => "{$target_dir}/{$target}",
                  '{target_app_root}' => $target_app_root);
    self::concat($out_file,
                 array("autoload_template.php", "lib_template.php"),
                 $vars);
    self::runCommand($out_file, "Failed bootstrapping target {$target}");

    if ($type == "binary")  {
      $exe_file = "${out_dir}/{$target_dir}/{$target}";
      self::concat($exe_file,
                   array("autoload_template.php", "exe_template.php"),
                   $vars);
    } else if ($type == "test") {
      $test_file = "${out_dir}/{$target_dir}/{$target}";
      self::concat($test_file,
                   array("autoload_template.php", "test_template.php"),
                   $vars);
    }
  }

  private static function concat($output_file, $templates, $replacements) {
    $out = "";
    foreach ($templates as $i => $tpl) {
      $contents =
          strtr(file_get_contents(__DIR__ . "/template/$tpl"), $replacements);
      if ($i > 0) {
        $contents = str_replace('<?php', '', $contents);
      }
      $out .= trim($contents) . "\n";
    }
    file_put_contents($output_file, $out);
    chmod($output_file, 0755);
  }

  /** Copies a file to the output directory. */
  private function placeFile($filename, $out_dir) {
    $dest_file = self::join_path($out_dir, $filename);
    $dest_dir = dirname($dest_file);
    if (!file_exists($dest_dir)) {
      mkdir($dest_dir, 0755, true);
    }
    copy($filename, $dest_file);
  }

  private static function checkSyntax($filename) {
    self::runCommand(
        "php -l $filename > /dev/null", "Invalid syntax in $filename");
  }

  private static function checkFileExists($filename) {
    if (!file_exists($filename)) {
      throw new Exception("Required file does not exist: $filename");
    }
  }

  private static function runCommand($cmd, $error="Error running cmd") {
    system("$cmd", $retval);
    if ($retval != 0) {
      throw new Exception($error);
    }
  }

  private static function join_path() {
    return implode("/", func_get_args());
  }
}
