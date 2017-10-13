# Bazel based PHP codebase

This project is a work in progress which builds on the bazel's philosophy of
reproducible builds, targeting PHP. Bazel, used with these PHP build rules
provides several advantages:

1. Allows specifying encapsulated targets. E.g. a small PHP library with a few
   source files.
2. Binds code modules through specified dependencies. You don't have to care
   about `include` or `require`, only the `use` keyword for `namespace`s and
   class autoloading. Bootstrapping the libraries should take care of the rest.
3. Only affected targets get rebuilt. You don't have to run the entire test
   suite on each change, only the files which can actually be affected, provided
   you don't break the target encapsulation. Bazel does this out of the box.
4. Easy packaging for production. Build docker images with simple rules.

**Note:** This is still highly experimental and should not be used in
production.

## Current features

Build rules

* php_library - a set of PHP files which are checked and bootstrapped
* php_test - same as library, with an extra test runner executable named by the
  target
* php_executable - same as library, with an extra entry point named by the
  target
* php_image - same as executable, but as a docker image instead.

Workspace rules

* composer_php_library - simple wrapper for fetching a composer library and
  placing the vendor directory into {project_root}/external/{target_name}, you
  can simply reference this as a dependency, see the phpunit target as example.

Unit testing is performed with PHPUnit.

## Setup instructions

* Install bazel

* Install docker if you want to build images

Working with PHP 5.6. Planning to add support for PHP 7.0, 7.1

## Concepts and terminology

Since PHP is an interpreted language, a library and a binary don't fall in to
the conventional concept of those terms.

Building ensures that all source files have valid syntax and can reach runtime.
It also extracts only the required files from the entire source tree which get
executed. It is therefore easy to package and ship those files either as a
container or an application in a traditional sense.

Here we touch on the build rules associated with building PHP code:

* PHP library
* PHP executable
* PHP test

### PHP library

A library is a set of symbols defined in one or more files which live in the
same directory. A library should not execute code, apart from defining symbols
like constants, functions and classes. One library may depend on other libraries
living in other source tree directories.

To build a library is in essence to copy the source files into an output
directory, preserving the path structure and not modifying any code.

### PHP executable

A PHP executable is a single script file which executes PHP code, meaning it
takes inputs and produces outputs. An executable may depend on PHP libraries.
The main file should have a `function main($args) {}` and this is considered
the entry point method.

Building an executable is achieved through copying the executable source files,
all transitive dependencies (i.e. libraries) to their respective directories and
also produce a bootstrapping entry point which handles autoloading of symbols,
includes the executable sources and calls the `main()`.

### PHP test

A PHP test is a library which can execute test methods from test case classes. A
test usually depends at least on one library or an executable.

To build a PHP test is similar as to building a library, the main difference is
we also produce an executable file which runs all the test cases.


## Bootstrapping

Bootstrapping a PHP target is like doing a dry-run on the source code, which
implies loading all the sources to try and find dependency issues before actual
runtime.

This is achieved by generating an autoload function with whitelisted sources as
bazel does not remove files of a built target if you remove a dependency to it
from another target. This holds for php_library, php_executable and php_test.

When building a library, the source files are only loaded as to find any issue
in static references outside scoped code (e.g. outside classes, such as an
extended class). This ensures that if we, for example extend a class from an
external dependency, that the base class can be autoloaded (i.e. is
whitelisted).

For executables and tests, the bootstrap process is the same and the autoload
function generated for the executable script or test runner also has whitelisted
sources. The only difference is we also generate the code to achieve the
runtime: invoking tests or running the main().

## TODO

Check that dependencies are actually used, i.e. need a build cleaner.

Devise a way to automatically add dependencies based on PHP use statements.

Install PHP beautifier (can't do newlines properly)

`sudo pear install channel://pear.php.net/PHP_Beautifier-0.1.15`

Install php-cs-fixer (can't do indent of 2 spaces)

`composer global require friendsofphp/php-cs-fixer`
