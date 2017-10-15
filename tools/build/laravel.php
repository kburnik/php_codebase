#!/usr/bin/env php
<?php

# Creates a bazel and PHP supported Laravel scaffolding app.

namespace tools\build;

use \Exception;
use \ErrorException;

require_once(__DIR__ . '/Options.php');

class Laravel {
  const NAMESPACE_PATTERN = '/^namespace (.*?);\s*$/m';

  // Patterns referring to transient namespaces.
  private static $usePatterns = [
      '/^use (.*);$/',      # Must have namespace at \1
      '/^(.*)::class(.*)$/' # Must have namespace at \1
  ];

  private static $tempFiles = [];

  private $targetDir;
  private $rootDir;
  private $name;
  private $targetShortPath;
  private $rootNamespace;

  public static function main($args) {
    register_shutdown_function(array('\\Tools\\build\\Laravel', 'cleanUp'));
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
      throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    });

    $opts = Options::parse($args);
    if ($opts->has('verbose')) {
       $opts->show();
    }

    $rootDir = realpath(__DIR__ . "/../..");
    $outDir = $opts->get_string("out");
    $targetDir = $rootDir . "/{$outDir}";
    $name = basename($targetDir);

    $laravel = new Laravel($targetDir, $name, $rootDir);
    $laravel->createProject();
  }

  private function __construct($targetDir, $name, $rootDir) {
    $this->targetDir = $targetDir;
    $this->name = $name;
    $this->rootDir = $rootDir;
    $this->targetShortPath =
        substr($this->targetDir, strlen($this->rootDir) + 1);
    $this->rootNamespace = str_replace("/", "\\", $this->targetShortPath);
  }

  private function createProject() {
    echo "Dir: {$this->targetDir}\n";
    echo "Target: {$this->name}\n";

    $parentDir = dirname($this->targetDir);
    if (!file_exists($parentDir)) {
      mkdir($parentDir, 0755, true);
    }
    $cwd = getcwd();

    chdir($parentDir);

    // Create the scaffolding project.
    if (!file_exists("{$this->targetDir}/artisan")) {
      self::runCommand([
          'composer',
          'create-project',
          '--prefer-dist',
          'laravel/laravel',
          $this->name]);
    } else {
      echo "Skipped composer step: artisan already present.\n";
    }

    // Remove the vendor dir from source tree.
    $vendorDir = $this->targetDir . "/vendor";

    if (file_exists($vendorDir)) {
      $this->rmdir($vendorDir);
    } else {
      echo "Skipped remove vendor step: already missing.\n";
    }

    $removed = $this->removeFiles(
        ['composer.*', 'phpunit.xml', 'readme.md', '.env.example']);
    if ($removed == 0) {
      echo "Skipped file removal: already removed\n";
    }

    if ($this->renameDirectories() == 0) {
      echo "Skipped directory renaming: already at lowercase\n";
    }

    if ($this->updateSourceFiles() == 0) {
      echo "Skipped source file updates: already up to date\n";
    }

    if ($this->createBuildFiles() == 0) {
      echo "Skipped create BUILD files: already up to date\n";
    }
  }

  private function updateBootstrapAutoload() {
    // "{$this->targetDir}/bootstrap/autoload.php":
  }

  private function createBuildFiles() {
    $srcs = $this->getSourceFiles();

    $namespaceTarget = [];
    // Build the dependency graph.
    foreach ($srcs as $src) {
      $content = file_get_contents($src);
      if (preg_match_all(self::NAMESPACE_PATTERN, $content, $matches)) {
        foreach ($matches[1] as $match) {
          $namespaceTarget[$match] =
              "//" . substr(dirname($src), strlen($this->rootDir) + 1);
        }
      }
    }

    uksort(
        $namespaceTarget,
        array('\tools\build\Laravel', 'compareLengthDesc'));

    // TODO(kburnik): Refactor this utter mess.
    $namespaces = array_keys($namespaceTarget);
    $namespaceDepFolders = [];
    foreach ($srcs as $src) {
      $content = file_get_contents($src);
      $shortPath =  substr($src, strlen($this->rootDir) + 1);
      $srcTarget = "//" . dirname($shortPath);

      foreach (self::$usePatterns as $usePattern) {
        $matches = [];
        $usePattern .= "m";
        if (!preg_match_all($usePattern, $content, $matches))  {
          continue;
        }

        foreach ($matches[1] as $match) {
          foreach ($namespaces as $namespace) {
            if (strpos($match, $namespace) === false) {
              continue;
            }

            $depDir = dirname($src);
            $depTarget = $namespaceTarget[$namespace];

            if ($depTarget == $srcTarget) {
              continue;
            }

            echo "{$depDir}: {$depTarget}\n";
            $namespaceDepFolders[$depDir][$depTarget] = true;

            // Break on first match.
            break;
          } // over namespaces
        } // over matches
      } // over usePatterns
    } // over srcs

    // Normalize deps.
    foreach ($namespaceDepFolders as $depDir => $map) {
      $namespaceDepFolders[$depDir] = array_keys($map);
    }

    echo "Namespace to Target ";
    print_r($namespaceTarget);


    echo "Deps ";
    print_r($namespaceDepFolders);

    $dirs = array();
    $index = 0;
    foreach ($srcs as $src) {
      $dir = dirname($src);
      if (!array_key_exists($dir, $dirs)) {
        $dirs[$dir] = 0;
      }
      $dirs[$dir] = $index++;
    }
    $dirs = array_values(array_flip($dirs));

    $numBuildFiles = 0;
    foreach ($dirs as $dir) {
      $eol = "\n";
      $targetName = basename($dir);
      if ($targetName == "")
        $targetName = $this->$name;

      $computedDeps = array();
      if (array_key_exists($dir, $namespaceDepFolders)) {
        $computedDeps = $namespaceDepFolders[$dir];
      }

      $deps = array_merge($computedDeps, ["@laravel//:laravel"]);
      $vars =  [
        '{targetName}' => $targetName,
        '{targetShortPath}' => $this->targetShortPath,
        '{deps}' => str_replace("\\", "", json_encode($deps)),
      ];

      $numBuildFiles +=
          $this->createBuildFile($dir, "laravel_lib.BUILD.tpl", $vars);
    }

    return $numBuildFiles;
  }

  private function createBuildFile($directory, $templateFile, $vars) {
    $template = file_get_contents(__DIR__ . "/template/$templateFile");
    $buildFile = "{$directory}/BUILD";

    $content = strtr($template, $vars);

    if (file_exists($buildFile) && file_get_contents($buildFile) == $content) {
      return 0;
    }

    file_put_contents($buildFile, $content);
    return 1;
  }

  private function updateSourceFiles() {
    // Mapping of old namespaces to new ones.
    $namespaceMap = [];
    $srcs = $this->getSourceFiles();
    $modifyCount = 0;
    foreach ($srcs as $src) {
      $content = file_get_contents($src);
      $namespace =
          str_replace(
              '/',
              '\\',
              dirname(substr($src, strlen($this->rootDir) + 1)));

      $newContent = $content;

      // Namespace fix.
      $matches = [];
      if (preg_match_all(self::NAMESPACE_PATTERN, $newContent, $matches)) {
        foreach ($matches[1] as $match) {
          if ($match != $namespace) {
            $namespaceMap[$match] = $namespace;
          }
        }
      }

      // Braces position to K&R style.
      $newContent =
          preg_replace('/(.*?)\n\s*{/s', '\1 {', $newContent);

      // Double spaces instead of quadruple.
      if (!preg_match_all('/^\s\s(?!\s).*$/m', $newContent, $matches)) {
        $newContent = str_replace(str_repeat(" ", 4),
                                  str_repeat(" ", 2),
                                  $newContent);
      }

      $modifyCount += $this->safeReplaceSource($src, $newContent);
    }

    uksort($namespaceMap, array('\tools\build\Laravel', 'compareLengthDesc'));

    // Update namespace references.
    if (count($namespaceMap)) {
      foreach ($srcs as $src) {
        $newContent = file_get_contents($src);
        $patterns = array_merge(['/^namespace (.*);$/'], self::$usePatterns);
        $newContent = self::replaceForFirstMatchingPatternInLines(
            $patterns, $namespaceMap, $newContent);
        $modifyCount += $this->safeReplaceSource($src, $newContent);
      }
    }

    return $modifyCount;
  }

  private static function replaceForFirstMatchingPatternInLines(
        $patterns, $replacements, $subject) {
    $lines = explode("\n", $subject);

    foreach ($lines as $i => $line) {
      foreach ($patterns as $pattern) {
        if (preg_match($pattern, $line)) {
          $lines[$i] = strtr($line, $replacements);
          if ($line != $lines[$i]) {
            break;
          }
        }
      }
    }

    return implode("\n", $lines);
  }

  private function renameDirectories() {
    $dirs = $this->getDirectories();
    $depthMap = [];
    foreach ($dirs as $dir) {
      $key = substr_count($dir, '/');
      $depthMap[$dir] = $key;
    }

    arsort($depthMap);
    $sortedDirs = array_keys($depthMap);
    $renameCount = 0;

    foreach ($sortedDirs as $dir) {
      $shortPath = substr($dir, strlen($this->targetDir) + 1);
      $newShortPath =
          dirname($shortPath) . "/" . strtolower(basename($shortPath));
      // The dirname of a "current_level_dir" is ".", so check that too.
      if ($shortPath == $newShortPath || "./$shortPath" == $newShortPath) {
        continue;
      }

      echo "rename($shortPath, $newShortPath)\n";
      $newName = "{$this->targetDir}/{$newShortPath}";
      rename($dir, $newName);
      $renameCount++;
    }

    return $renameCount;
  }

  private function safeReplaceSource($src, $newContent) {
    $oldContent = file_get_contents($src);
    if ($oldContent == $newContent)
      return 0;

    // Check syntax before saving.
    try {
      $this->checkSyntax($newContent);
    } catch(Exception $ex) {
      print_r($newContent);
      throw $ex;
    }
    file_put_contents($src, $newContent);

    return 1;
  }

  /** Checks PHP source code syntax. Throws with error message on failure. */
  private function checkSyntax($sourceCode) {
    $this->withTempFileOfContents($sourceCode, function($tempFile) {
      self::runCommand(["php", "-l", $tempFile, "> /dev/null"]);
    });
  }

  private function withTempFileOfContents($contents = "", $callback) {
    $filename = tempnam("/tmp", "temp_php");
    try {
      self::$tempFiles[] = $filename;
      file_put_contents($filename, $contents);
      call_user_func($callback, $filename);
    } finally {
      unlink($filename);
    }
  }

  private function removeFiles($patterns) {
    $removed = 0;
    foreach ($patterns as $pattern) {
      foreach (glob("{$this->targetDir}/{$pattern}") as $file) {
        $this->rm($file);
        $removed++;
      }
    }

    return $removed;
  }

  private function getSourceFiles() {
    return self::rglob("{$this->targetDir}/*.php");
  }

  private function getDirectories() {
    return self::rglob("{$this->targetDir}/*", GLOB_ONLYDIR);
  }

  private function rm($file) {
    $this->checkTargetDir($file);
    if (!unlink($file)) {
      throw new Exception("Could not remove file: $file");
    }
  }

  private function rmdir($dir) {
    $this->checkTargetDir($dir);
    self::runCommand(['rm', '-rf', $dir]);
  }

  private function checkTargetDir($dir) {
    $dir = realpath($dir);
    if (substr($dir, 0, strlen($this->targetDir) + 1) !=
        $this->targetDir . "/") {
      throw new Exception(
          "Can't operate on files below: {$this->targetDir}. Got: {$dir}");
    }
  }

  public static function cleanUp() {
    foreach (self::$tempFiles as $filename) {
      if (file_exists($filename)) {
        unlink($filename);
      }
    }
  }

  private static function compareLengthDesc($a, $b) {
    return strlen($a) < strlen($b);
  }

  private static function runCommand($cmd) {
    $cmdStr = implode(" ", $cmd);
    system($cmdStr, $retval);
    if ($retval != 0) {
      throw new Exception("Failed command: {$cmdStr}");
    }
  }

  private static function rglob($pattern, $flags = 0) {
    $files = glob($pattern, $flags);
    foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
      $files =
          array_merge($files, self::rglob($dir.'/'.basename($pattern), $flags));
    }
    return $files;
  }
}

Laravel::main(array_slice($argv, 1));
