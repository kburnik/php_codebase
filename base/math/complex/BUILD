load("//tools/build:php.bzl", "php_library", "php_test")

php_library(
  name="complex",
  srcs=["Complex.php"],
  deps=[
    "//base/except",
    "//base/type:immutable",
  ],
)

php_test(
  name="complex_test",
  srcs=["ComplexTest.php"],
  deps=[
    ":complex",
  ],
  size="small"
)
