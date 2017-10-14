load("//tools/build:php.bzl", "php_binary", "php_test", "php_image")
load("@io_bazel_rules_docker//container:image.bzl", "container_image")

php_binary(
  name="calc",
  srcs=["Calc.php"],
  deps=[
    "//base/math/complex:complex"
  ]
)

php_test(
  name="calc_test",
  srcs=["CalcTest.php"],
  deps=[
    ":calc"
  ],
  size="small",
)

filegroup(name="calc_files", srcs=[":calc"])

php_image(
  name="calc_img",
  srcs=["Calc.php"],
  deps=[
    "//base/math/complex:complex"
  ],
  # cmd="find 2>/dev/null | grep -e calc -e Calc"
)
