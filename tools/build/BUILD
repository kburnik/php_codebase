
# We can reuse sh_binary for an executable PHP script which has the shebang.
# Notice that dependencies are listed in the data section to ensure this runs
# entirely from a bazel runfiles dir. Ideally, this would be a php_binary, but
# then we run into a self-bootstrapping problem.
sh_binary(
  name="build",
  srcs=["build.php"],
  data=[
    "//tools/build/template",
    "Builder.php",
    "Options.php"
  ],
  visibility=["//visibility:public"]
)

sh_binary(
  name="laravel",
  srcs=["laravel.php"],
  data=[
    "//tools/build/template",
    "Options.php"
  ],
  visibility=["//visibility:public"]
)
