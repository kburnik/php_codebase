# Bazel PHP build rules (php_library, php_binary, php_test, etc.).

load(
    "@io_bazel_rules_docker//lang:image.bzl",
    "dep_layer",
    "app_layer",
)

def _build_impl(ctx):
  direct_src_files = [f.path for f in ctx.files.srcs]

  # Traverse dependencies, the dep files for the current target.
  deps_src_files = depset()
  for dep in ctx.attr.deps:
    deps_src_files += dep[DefaultInfo].files
    if hasattr(dep[DefaultInfo], 'files_to_run'):
      deps_src_files += dep[DefaultInfo].default_runfiles.files

  lib_outputs = depset()
  out_dir = ""
  for src in ctx.files.srcs:
    out_file = ctx.actions.declare_file(
        (src.path if ctx.attr.recursive else src.basename))
    lib_outputs += [out_file]
    out_dir = out_file.root.path


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
                (["--bootstrap"] if ctx.attr.bootstrap else []),
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

_php_binary_rule = rule(
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
  kwargs['type'] = "library";
  _build_rule(**kwargs)


def php_binary(**kwargs):
  kwargs['type'] = "binary"
  kwargs['bootstrap'] = True
  _php_binary_rule(**kwargs)


def php_test(**kwargs):
  kwargs['deps'] += ['@phpunit//:phpunit']
  kwargs['type'] = "test";
  kwargs['bootstrap'] = True
  _php_test(**kwargs)


def php_image(name, base=None, deps=[], layers=[], **kwargs):
  """Constructs a container image wrapping a php_binary target.
  Args:
    layers: Augments "deps" with dependencies that should be put into
           their own layers.
    **kwargs: See php_binary.
  """
  DEFAULT_BASE = "@php56_base//image"
  binary_name = name + "_img_bin"

  php_binary(name=binary_name, deps=deps + layers, **kwargs)

  index = 0
  base = base or DEFAULT_BASE
  for dep in layers:
    this_name = "%s.%d" % (name, index)
    dep_layer(name=this_name, base=base, dep=dep)
    base = this_name
    index += 1

  app_layer(name=name, base=base, binary=binary_name, layers=layers)
