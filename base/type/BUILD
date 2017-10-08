load("//tools/build:php.bzl", "php_library", "php_test")

php_library(
  name="immutable",
  srcs=["Immutable.php"],
  deps=["//base/except"],
  visibility=["//visibility:public"]
)

php_test(
  name="immutable_test",
  srcs=["ImmutableTest.php"],
  deps=[
    ":immutable",
    "//base/type/testing"
  ],
  size="small"
)
