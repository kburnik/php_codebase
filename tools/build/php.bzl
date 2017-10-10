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


def _build_lib_impl(ctx):
  direct_src_files = [f.path for f in ctx.files.srcs]

  # List dependencies
  deps_src_files = depset()
  for dep in ctx.attr.deps:
    deps_src_files += dep[DefaultInfo].files

  lib_outputs = depset()
  out_dir = ""
  for src in ctx.files.srcs:
    out_file = ctx.actions.declare_file(
        (src.path if ctx.attr.recursive else src.basename))
    lib_outputs += [out_file]
    out_dir = out_file.root.path

  bootstrap = ["--bootstrap"] if ctx.attr.bootstrap else []
  ctx.actions.run(
      inputs=ctx.files.srcs + list(deps_src_files),
      outputs=list(lib_outputs),
      arguments=["--out", out_dir] +
                ["--src"] + direct_src_files +
                ["--dep"] + [f.short_path for f in deps_src_files] +
                ["--target", ctx.label.name] +
                bootstrap,
      progress_message="Building lib %s" % ctx.label.name,
      executable=ctx.executable._build_lib)

  return [DefaultInfo(files=lib_outputs + deps_src_files)]

def _php_test_impl(ctx):
  # Build all the files required for testing first.
  res = _build_lib_impl(ctx)
  test_deps = res[0].files

  direct_src_files = [f.path for f in ctx.files.srcs]
  ctx.actions.run(
      inputs=test_deps,
      outputs=[ctx.outputs.executable],
      arguments=[ctx.outputs.executable.path] + direct_src_files,
      progress_message="Testing %s" % ctx.label.name,
      executable=ctx.executable._gentest)
  runfiles = ctx.runfiles(files=[ctx.outputs.executable] + list(test_deps))
  return [DefaultInfo(runfiles=runfiles)]


def _php_executable_impl(ctx):
  # Build all the files required for testing first.
  res = _build_lib_impl(ctx)
  exe_deps = res[0].files

  direct_src_files = [f.path for f in ctx.files.srcs]
  ctx.actions.run(
      inputs=exe_deps,
      outputs=[ctx.outputs.executable],
      arguments=[ctx.outputs.executable.path] + direct_src_files,
      progress_message="Running %s" % ctx.label.name,
      executable=ctx.executable._genexe)
  runfiles = ctx.runfiles(files=[ctx.outputs.executable] + list(exe_deps))
  return [DefaultInfo(runfiles=runfiles)]



# Common for library, testing & running.
build_common = {
  "attrs": {
      "srcs": attr.label_list(allow_files=True),
      "deps": attr.label_list(allow_files=False),
      "recursive": attr.bool(default=False),
      "bootstrap": attr.bool(default=True),
      "_build_lib": attr.label(executable=True,
                               cfg="host",
                               allow_files=True,
                               default=Label("//:build_lib")),
      "_genexe": attr.label(executable=True, cfg="host",
                            allow_files=True,
                            default=Label("//:genexe")),
      "_gentest": attr.label(executable=True, cfg="host",
                             allow_files=True,
                             default=Label("//:gentest")),
  },
}

_build_lib_rule = rule(
  implementation=_build_lib_impl,
  **build_common
)

_php_executable_rule = rule(
  implementation=_php_executable_impl,
  executable=True,
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
  _build_lib_rule(**kwargs)


def php_executable(**kwargs):
  if kwargs['name'] != 'autoload':
    kwargs['deps'] += ['//:autoload']
  _php_executable_rule(**kwargs)


def php_test(**kwargs):
  kwargs['deps'] += ['//:autoload', '//:vendor_phpunit']
  _php_test(**kwargs)

