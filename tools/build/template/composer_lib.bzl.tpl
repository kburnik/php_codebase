# Bazel rules for building a composer php library.

def _trim_start(line, prefix):
  if line.startswith(prefix):
    return line[len(prefix):]
  else:
    return line

# For now, just copies all the files to the external target dir.
def _composer_php_library_impl(ctx):
  out_files = depset()
  for src in ctx.files.srcs:
    out_filename = _trim_start(src.path, "external/%s/vendor/" % ctx.label.name)
    out = ctx.actions.declare_file(out_filename)
    out_files += [out]
    ctx.actions.run_shell(
      inputs=[src],
      outputs=[out],
      mnemonic="CopySrcFile",
      progress_message="Copy external/%s/%s" % (ctx.label.name, out_filename),
      command='cp {src} {dest}'.format(src=src.path, dest=out.path))
  return [DefaultInfo(files=out_files)]

composer_php_library = rule(
  implementation=_composer_php_library_impl,
  attrs={
    "srcs": attr.label_list(allow_files=True),
  },
)
