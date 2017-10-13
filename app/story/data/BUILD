load("//tools/build:php.bzl", "php_resource")

php_resource(
  name="data",
  srcs=glob(["*.txt"]),
  visibility=["//app/story:__subpackages__"],
)
