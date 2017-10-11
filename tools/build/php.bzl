# Bazel PHP build rules (php_library, php_binary, php_test, etc.).

def _build_impl(ctx):
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

  bootstrap = []
  if ctx.attr.bootstrap:
    bootstrap = ["--bootstrap"]

  if ctx.attr.type != "library":
    lib_outputs += [ctx.outputs.executable]

  ctx.actions.run(
      inputs=ctx.files.srcs + list(deps_src_files),
      outputs=list(lib_outputs),
      arguments=["--type", ctx.attr.type] +
                ["--out", out_dir] +
                ["--src"] + direct_src_files +
                ["--dep"] + [f.short_path for f in deps_src_files] +
                ["--target", ctx.label.name] +
                bootstrap,
      progress_message="Building lib %s" % ctx.label.name,
      executable=ctx.executable._build)

  if ctx.attr.type == "library":
    return [DefaultInfo(files=lib_outputs + deps_src_files)]
  else:
    runfiles = ctx.runfiles(
        files=[ctx.outputs.executable] + list(lib_outputs + deps_src_files))
    return [DefaultInfo(runfiles=runfiles)]


# Common for library, testing & running.
build_common = {
  "attrs": {
      "srcs": attr.label_list(allow_files=True),
      "deps": attr.label_list(allow_files=False),
      "recursive": attr.bool(default=False),
      "bootstrap": attr.bool(default=True),
      "type": attr.string(default="library"),
      "_build": attr.label(executable=True,
                           cfg="host",
                           allow_files=True,
                           default=Label("//tools/build:build")),
  },
}

_build_rule = rule(
  implementation=_build_impl,
  **build_common
)

_php_executable_rule = rule(
  implementation=_build_impl,
  executable=True,
  **build_common
)

_php_test = rule(
  implementation=_build_impl,
  test=True,
  **build_common
)


def php_library(**kwargs):
  if kwargs['name'] != 'autoload':
    kwargs['deps'] += ['//:autoload']
  kwargs['type'] = "library";
  _build_rule(**kwargs)


def php_executable(**kwargs):
  if kwargs['name'] != 'autoload':
    kwargs['deps'] += ['//:autoload']
  kwargs['type'] = "executable"
  kwargs['bootstrap'] = True
  _php_executable_rule(**kwargs)


def php_test(**kwargs):
  kwargs['deps'] += ['//:autoload', '//:vendor_phpunit']
  kwargs['type'] = "test";
  kwargs['bootstrap'] = True
  _php_test(**kwargs)

