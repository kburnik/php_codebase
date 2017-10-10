load("//tools/build:php.bzl", "php_executable", "php_test")

php_executable(
  name="calc",
  srcs=["Calc.php"],
  deps=[
    "//base/math/complex:complex"
  ]
)

php_test(
  name="calc_test",
  srcs=["CalcTest.php"],
  deps=[":calc"],
)
