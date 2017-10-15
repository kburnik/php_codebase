# Bazel based PHP codebase

This project is a work in progress which builds on the bazel's philosophy of
reproducible builds, targeting PHP. Bazel, used with these PHP build rules
provides several advantages:

1. Allows specifying encapsulated targets. E.g. a small PHP library with a few
   source files.
2. Binds code modules through specified dependencies. You don't have to care
   about `include` or `require`, only the `use` keyword for `namespace`s and
   class autoloading - bootstrapping the libraries should take care of the rest.
3. Only affected targets get rebuilt. You don't have to run the entire test
   suite on each change, only the files which can actually be affected, provided
   you don't break the target encapsulation. Bazel does this out of the box.
4. Easy packaging for production. Build docker images with simple rules.

**Note:** This is still highly experimental and should not be used in
production.

The first example application in this repository is a clunky complex number
calculator.

For example, you may run:

```sh
bazel run app/calc:calc -- "3+3i * 2+4i"
```

To get the output:
```
-6 + 18i
```

This is a simplified dependency graph of the project:

```
  base/except:except (exceptions)  @phpunit//:phpunit (external composer lib)
            ^                                                         ^
            |                                                         |
  base/type:immutable (immutable object class)                        |
            ^       ^                                                 |
            |       |                                                 |
            |      base/type:immutable_test (unit test) --------------|
            |                                                         |
  base/math/complex:complex (complex number representation)           |
            ^       ^                                                 |
            |       |                                                 |
            |      base/math/complex:complex_test (unit test) --------|
            |                                                         |
  app/calc:calc (calculator app)                                      |
                    ^                                                 |
                    |                                                 |
                   app/calc:calc_test (unit test for the app) ________|

```

The second example is an app which reads an integer index and outputs a story
associated with that index. This serves as an example for accessing static
data from source files.

```
        app/story/data (static data)  @phpunit//:phpunit (external composer lib)
            ^                     ^          ^
            |                     |          |
  app/story:story (story app)     |          |
                   ^              |          |
                   |              |          |
                  app/story:story_test (unit test)

```

## Current features

Build rules

* **php_library** - a set of PHP files which are checked and bootstrapped.
* **php_binary** - same as library, with an extra entry point named by the
  target.
* **php_test** - same as library, with an extra test runner executable named by
  the target.
* **php_image** - same as binary, but as a docker image instead.
* **php_resource** - a static resource library, e.g. for reading static files.

Workspace rules

* **composer_repository** - a wrapper for fetching a composer library and
  placing the vendor directory into {project_root}/external/{target_name}, you
  can simply reference this as a dependency, see the phpunit target as example.

## Setup instructions

* Install bazel

* Install docker if you want to build images

* Pull in this repository with git

Working with PHP 5.6. Planning to add support for PHP 7.0, 7.1

## Concepts and terminology

Since PHP is an interpreted language, a library and a binary don't fall in to
the conventional concept of those terms.

Building ensures that all source files have valid syntax and can reach runtime.
It also extracts only the required files from the entire source tree which get
executed. It is therefore easy to package and ship those files either as a
container or an application in the traditional sense (folder with PHP files).

Here we touch on the build rules associated with building PHP code:

* PHP library
* PHP binary
* PHP test

### PHP library

A library is a set of symbols defined in one or more files which live in the
same directory. A library should not execute code, apart from defining symbols
like constants, functions and classes. One library may depend on other libraries
living in other source tree directories.

To build a library is in essence to copy the source files into an output
directory, preserving the path structure and not modifying any code.

### PHP binary

A PHP binary is a single script file which executes PHP code, meaning it
takes inputs and produces outputs. An executable may depend on PHP libraries.
The main file should have a class with `public static function main($args) {}`,
similar to Java or C# and this is considered the entry point method.

Building an executable is achieved through copying the executable source files,
all transitive dependencies (i.e. libraries) to their respective directories and
also produce a bootstrapping entry point which handles autoloading of symbols,
includes the executable sources and calls
`YourMainClass::main(array_slice($argv, 1))`.

### PHP test

A PHP test is a library which can execute test methods from test case classes. A
test usually depends on at least one library or an executable.

To build a PHP test is similar as to building a library, the main difference is
we also produce an executable file which runs all the test cases.


## Bootstrapping

Bootstrapping a PHP target is like doing a dry-run on the source code, which
implies loading all the sources to try and find dependency issues before actual
runtime.

This is achieved by generating an autoload function with whitelisted sources, as
bazel does not remove files of a built target if you remove a dependency to it
from another target. This holds for php_library, php_binary and php_test.

When building a library, the source files are only loaded so to find any issue
in static references outside scoped code (e.g. outside classes, such as an
extended class). This ensures that if we, for example extend a class from an
external dependency, that the base class can be autoloaded (i.e. is
whitelisted).

For executables and tests, the bootstrap process is the same and the autoload
function generated for the executable script or test runner also has whitelisted
sources. The only difference is we also generate the code to achieve the
runtime: invoking tests or running the main().


## Style guide

There's no particular style guide imposed for the code layout, however the build
rules do expect some structure in your source files.

### Namespacing

Each source file should have it's namespace which matches the full directory
path from the project root. So having a foo/bar/baz.php would have:

```php
namespace foo\bar;
```

And the Baz class should be referenced as:

```php
use foo\bar\Baz;
```

For external dependencies, such as composer libraries, use their canonical
namespaces. For example:

```php
use PHPUnit\Framework\TestCase;
```

It's common to forget including Exception from the root namespace:

```php
use \Exception;
```

### Classes

Each source file should encapsulate the code into a class, similar to Java. The
class name should exactly match the source file's basename without the
extension. Example:

File: foo/bar/MyClass.php

```php
<?php

namespace foo\bar;

class MyClass {}
```

Multiple classes per file are allowed, but discouraged. Only if you consider
those classes as private, then place them into the same file.

### Libraries

Libraries should not execute code, only declare symbols such as classes,
interfaces, traits, functions and constants.

Using `define()` is discouraged, rather have a `Constants` class and put them
there.

### Binaries

A binary should have one top-level class named after the file, for example:

File: foo/bar/AddArgs.php

```php
<?php

namespace foo\bar;

class AddArgs {
  public static function main($args) {
    echo array_sum($args) . "\n";
    return 0;
  }
}
```

The class must have the `public static function main()` which is the entry
point.

Also notice how you should provide an exit code, similar how a C program would
return 0 on success. If you return nothing, the bootstrapping script calling
main will convert this to 0.

Binaries, as well as libraries can have a test target. You only need to specify
the target in the php_test deps.

### Static files

For accessing static files such as templates, default data and other content
which should live in separate files, rather than source code, use the
`php_resource` rule. This will create a library with a `StaticResource` class
which you can access in the source files. See the app/story as an example.

## TODO

* php_image target requires src and deps, it should also be able to work with
  simply referencing a php_binary as to avoid having conflicting actions.

* Consider doing apriory symbol bootstraping - for example, find all PHP tokens
  of type T_STRING which refer to a class/interface/trait, resolve their
  namespace and load them. This proved to be harder to do than initially
  anticipated.

* Check that dependencies are actually used, i.e. need a build cleaner.

* Devise a way to automatically add dependencies based on PHP use statements.

* Install PHP beautifier (can't do newlines properly)

`sudo pear install channel://pear.php.net/PHP_Beautifier-0.1.15`

* Install php-cs-fixer (can't do indent of 2 spaces)

`composer global require friendsofphp/php-cs-fixer`


## Notes

These notes are mostly bazel related tips and tricks which I occasionally find
useful:

### Find and run all tests

```
grep -r --include=BUILD -oP "name=\"(\K\w+_test)" | \
  sed s#/BUILD## | \
  xargs bazel test --test_output=errors
```

### Build all targets

```
grep -r --include=BUILD -oP "name=\"(\K\w+)" | \
  sed s#/BUILD## | \
  xargs bazel build
```
