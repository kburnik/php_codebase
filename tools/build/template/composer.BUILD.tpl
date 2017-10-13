load(":composer_lib.bzl", "composer_php_library")

composer_php_library(
  name="%{NAME}",
  srcs=glob(["vendor/**/*.php"]),
  visibility=["//visibility:public"],
)
