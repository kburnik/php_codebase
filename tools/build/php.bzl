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


def _place_files_impl(ctx):
  # The list of arguments we pass to the script.
  direct_src_files = [f.path for f in ctx.files.srcs]
  transitive_src_files = \
      [f.path for f in get_transitive_srcs(ctx.files.srcs, ctx.attr.deps,
                                           include_srcs=False)]
  syntax_check_result = ctx.actions.declare_file("_syntax." + ctx.label.name)

  # Filter to php files for syntax checking.
  php_files = []
  for file in direct_src_files:
    if file.endswith('.php'):
      php_files.append(file)

  ctx.actions.run(
    inputs=ctx.files.srcs,
    outputs=[syntax_check_result],
    arguments=[syntax_check_result.path] + php_files,
    progress_message="Checking PHP syntax of %s" % ctx.label.name,
    executable=ctx.executable._check_syntax)

  outputs = depset()
  for dep in ctx.attr.deps:
    outputs += dep[DefaultInfo].files

  src_copies = depset()
  outputs += [syntax_check_result]
  for src in ctx.files.srcs:
    src_copy = ctx.actions.declare_file(
        src.path if ctx.attr.recursive else src.basename)
    src_copies += [src_copy]
    outputs += [src_copy, syntax_check_result]
    ctx.actions.run_shell(
      outputs=[src_copy],
      inputs=[src],
      progress_message="Copy to output dir: %s" % src.path,
      command="cp {src} {dest}".format(src=src.path, dest=src_copy.path))

  transitive_sources = get_transitive_srcs(ctx.files.srcs, ctx.attr.deps)
  lib_files = depset()
  if ctx.attr.bootstrap:
    lib_file = ctx.actions.declare_file(ctx.label.name + ".phplib")
    lib_files += [lib_file]
    ctx.actions.run(
        inputs=outputs,
        outputs=[lib_file],
        arguments=[lib_file.path] + direct_src_files,
        progress_message="Bootstraping %s" % ctx.label.name,
        executable=ctx.executable._bootstrap)

  return [DefaultInfo(files=outputs + lib_files),
          PhpFiles(transitive_sources=transitive_sources)]


def _php_test_impl(ctx):
  # Build all the files required for testing first.
  res = _place_files_impl(ctx)
  test_deps = res[0].files

  direct_src_files = [f.path for f in ctx.files.srcs]
  ctx.actions.run(
      inputs=test_deps,
      outputs=[ctx.outputs.executable],
      arguments=[ctx.outputs.executable.path] + direct_src_files,
      progress_message="Testing %s" % ctx.label.name,
      executable=ctx.executable._gentest)
  runfiles = ctx.runfiles(
      files=[f for f in test_deps] + [ctx.outputs.executable])
  return [DefaultInfo(runfiles=runfiles)]


# Common for library, testing & running.
build_common = {
  "attrs": {
      "srcs": attr.label_list(allow_files=True),
      "deps": attr.label_list(allow_files=False),
      "recursive": attr.bool(default=False),
      "bootstrap": attr.bool(default=True),
      "_check_syntax": attr.label(executable=True,
                                  cfg="host",
                                  allow_files=True,
                                  default=Label("//tools/build:check_syntax")),
      "_bootstrap": attr.label(executable=True,
                              cfg="host",
                              allow_files=True,
                              default=Label("//:bootstrap")),
      "_gentest": attr.label(executable=True, cfg="host",
                             allow_files=True,
                             default=Label("//:gentest")),
  },
}

_place_files_rule = rule(
  implementation=_place_files_impl,
  **build_common
)

_php_test = rule(
  implementation=_php_test_impl,
  test=True,
  **build_common
)


def php_library(**kwargs):
  if kwargs['name'] != 'autoload':
    kwargs['deps'] += ['//:autoload']
  _place_files_rule(**kwargs)


def php_test(**kwargs):
  kwargs['deps'] += ['//:autoload', '//:vendor_phpunit']
  _php_test(**kwargs)

