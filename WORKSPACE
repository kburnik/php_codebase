load("//tools/build:composer.bzl", "composer_repository")

composer_repository(
  name="phpunit",
  package="phpunit/phpunit",
  # TODO(kburnik): Implement sha256 check
  digest="sha256:deadbeef"
)

git_repository(
    name = "io_bazel_rules_docker",
    remote = "https://github.com/bazelbuild/rules_docker.git",
    tag = "v0.3.0",
)

load(
    "@io_bazel_rules_docker//container:container.bzl",
    "container_pull",
    container_repositories = "repositories",
)

container_repositories()

container_pull(
  name = "php56_base",
  registry = "index.docker.io",
  repository = "library/php",
  tag = "5.6-cli",
  # digest = "sha256:506e2d5852de1d7c90d538c5332bd3cc33b9cbd26f6ca653875899c505c82687",
)

