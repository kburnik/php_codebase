# Codebase example

Attempt of building a unified codebase for a company.
The main tool is bazel which allows for specififying
software modules such as libraries and their dependencies

A project may live in it's own source code repository
hence the build tool should handle all the synchronization.

We could further extend the idea to creating pull requests
touching multiple projects at once.

Working with PHP 5.6.

Install in root of codebase:

`composer require --dev phpunit/phpunit`

Add to path:

`PATH=$PATH:$HOME/codebase/vendor/bin`
