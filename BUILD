load("//tools/build:php.bzl", "php_library")

sh_binary(
  name="bootstrap",
  srcs=["bootstrap.php"],
  visibility=["//visibility:public"]
)

sh_binary(
  name="gentest",
  srcs=["gentest.php"],
  visibility=["//visibility:public"]
)

php_library(
  name="autoload",
  srcs=["autoload.php", "vendor/autoload.php"] + glob(["vendor/composer/**"]),
  deps=[],
  recursive=True,
  bootstrap=False,
  visibility=["//visibility:public"],
)

filegroup(
  name="vendor_phpunit_files",
  srcs=glob(include=["vendor/**"],
            exclude=[
              "vendor/phpunit/php-token-stream/tests/**",
              "vendor/phpunit/phpunit-mock-objects/tests/**",
              "vendor/phpunit/phpunit/tests/**",
              "vendor/phpunit/.*",
              "vendor/phpunit/**/ChangeLog*.md",
              "vendor/phpunit/**/.git*",
              "vendor/phpunit/LICENSE",
              "vendor/phpunit/README.md",
              "vendor/phpunit/build.xml",
              "vendor/phpunit/composer.json",
            ]),
)

php_library(
  name="vendor_phpunit",
  srcs=[":vendor_phpunit_files"],
  deps=[],
  recursive=True,
  bootstrap=False,
  visibility=["//visibility:public"],
)
