# Bazel PHP build rules (php_library, php_binary, php_test, etc.).

# A provider with one field, transitive_sources.
PhpFiles = provider()

def get_transitive_srcs(srcs, deps, include_srcs=True):
  """Obtain the source files for a target and its transitive dependencies.

  Args:
    srcs: a list of source files
    deps: a list of targets that are direct dependencies
  Returns:
    a collection of the transitive sources
  """
  trans_srcs = depset()
  for dep in deps:
    trans_srcs += dep[PhpFiles].transitive_sources
  if include_srcs:
    trans_srcs += srcs
  return trans_srcs


def _php_library_impl(ctx):
  # The list of arguments we pass to the script.
  direct_src_files = [f.path for f in ctx.files.srcs]
  transitive_src_files = \
      [f.path for f in get_transitive_srcs(ctx.files.srcs, ctx.attr.deps,
                                           include_srcs=False)]
  # Check syntax.
  ctx.actions.run(
      inputs=ctx.files.srcs,
      outputs=[ctx.outputs.check_syntax],
      arguments=[ctx.outputs.check_syntax.path] + direct_src_files,
      progress_message="Checking %s" % ctx.label.name,
      executable=ctx.executable._check_syntax)
  # Run files.
  ctx.actions.run(
      inputs=ctx.files.srcs,
      outputs=[ctx.outputs.bootstrap],
      arguments=[ctx.outputs.bootstrap.path] +
                ["--deps"] + transitive_src_files +
                ["--srcs"] + direct_src_files,
      progress_message="Running %s" % ctx.label.name,
      executable=ctx.executable._bootstrap)
  trans_srcs = get_transitive_srcs(ctx.files.srcs,
                                   ctx.attr.deps,
                                   include_srcs=True)
  return [PhpFiles(transitive_sources=trans_srcs)]


def _php_test_impl(ctx):
  # Build all the files required for testing first.
  _php_library_impl(ctx)

  direct_src_files = [f.path for f in ctx.files.srcs]
  ctx.actions.run(
      inputs=ctx.files.srcs,
      outputs=[ctx.outputs.executable],
      arguments=[ctx.outputs.executable.path] + direct_src_files,
      progress_message="Testing %s" % ctx.label.name,
      executable=ctx.executable._runtest)
  return [DefaultInfo(runfiles=ctx.runfiles(files=ctx.files.srcs))]


# Common for library, testing & running.
build_common = {
  "attrs": {
      "srcs": attr.label_list(allow_files=True),
      "deps": attr.label_list(),
      "_check_syntax": attr.label(executable=True,
                                  cfg="host",
                                  allow_files=True,
                                  default=Label("//tools/build:check_syntax")),
      "_bootstrap": attr.label(executable=True,
                              cfg="host",
                              allow_files=True,
                              default=Label("//:bootstrap")),
      "_runtest": attr.label(executable=True, cfg="host",
                             allow_files=True,
                             default=Label("//:runtest")),
  },
  "outputs": {"check_syntax": "%{name}.syntax.txt",
              "bootstrap": "%{name}.bootstrap.txt"},
}

php_library = rule(
  implementation=_php_library_impl,
  **build_common
)

php_test = rule(
  implementation=_php_test_impl,
  test=True,
  **build_common
)
