# Codebase example

Attempt of building a unified codebase for a company.
The main tool is bazel which allows for specififying
software modules such as libraries and their dependencies.

A project may live in it's own source code repository
hence the build tool should handle all the synchronization.

We could further extend the idea to creating pull requests
touching multiple projects at once.

## Current features

Build rules

* PHP library
* PHP test

Unit testing with PHPUnit

## Setup instructions

Working with PHP 5.6.

Install bazel.

Install in root of codebase:

`composer require --dev phpunit/phpunit`

Add to path (if want to run):

`PATH=$PATH:$HOME/codebase/vendor/bin`


## TODO

Install PHP beautifier (can't do newlines properly)

`sudo pear install channel://pear.php.net/PHP_Beautifier-0.1.15`

Install php-cs-fixer (can't do indent of spaces)

`composer global require friendsofphp/php-cs-fixer`
