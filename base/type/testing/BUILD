load("//tools/build:php.bzl", "php_library")

php_library(
  name="testing",
  srcs=glob(["*.php"]),
  deps=[
    "//base/type:immutable"
  ],
  visibility=["//visibility:public"],
  testonly=True
)
