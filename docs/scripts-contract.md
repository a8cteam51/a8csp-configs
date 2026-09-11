# Scripts contract

This document names the composer and npm script identifiers the reusable workflows in this
repository treat as their interface. A consuming project's `composer.json` and `package.json`
scripts must use these names for the corresponding reusable workflow to find and run them. It also
lists the files the release workflow reads from the consumer's tree.

## Fixed names

These names are hardcoded in the reusable workflow and are not configurable via a `workflow_call`
input.

| Script | Ecosystem | Workflow | Condition |
| --- | --- | --- | --- |
| `lint:scripts` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-scripts` input is `true` (default). |
| `lint:styles` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-styles` input is `true` (default). |
| `changelog:validate` | composer | `reusable-release.yml` | Runs in every release build, before the production install. |

## Default names (input-overridable)

These names are the default value of a `workflow_call` input; a caller may override them, but
matching the default avoids an unnecessary `with:` entry.

| Script | Ecosystem | Workflow | Input |
| --- | --- | --- | --- |
| `test` | composer | `reusable-phpunit.yml` | `composer-script` |
| `build` | npm | `reusable-playwright-e2e.yml` | `build-script` |
| `test:e2e` | npm | `reusable-playwright-e2e.yml` | `playwright-script` |

## Convention (consumer-declared, not enforced by the workflow)

`reusable-php-lint.yml` takes a required `scripts` array of composer script names and runs each as
its own matrix job; the workflow itself names none of them. The established convention across
consumers is a `lint:php` composer script that aggregates `lint:php:phpcs`, `lint:php:phpcs:tests`,
and `lint:php:phpstan`, passed as `'["lint:php:phpcs", "lint:php:phpcs:tests", "lint:php:phpstan"]'`.
The `lint:php:phpcs:tests` job runs the companion tests-profile ruleset over `tests/`; a consumer
that omits it lints production code but leaves its test code unchecked.

## Release

Besides the `changelog:validate` script, `reusable-release.yml` depends on these files in the
consumer's repository. Only the entry file can be changed through a `workflow_call` input.

| File | Requirement |
| --- | --- |
| `<plugin-slug>.php` | Main plugin file whose docblock header line `* Version: <version>` states the release version. The `entry-file` input names a different file. |
| `package.json` | Its `version` field must equal the plugin header `Version`. |
| `CHANGELOG.md` | Its first `## ` heading must name the same version, as `## <version> - <date>` or `## [<version>] - <date>`. The section under that heading becomes the release notes. |
| `.github/workflows/quality.yml` and `.github/workflows/tests.yml` | Both workflows must have a successful `push` run on the tagged commit, or the release fails before it publishes. |

## Out of scope

The reusable release-smoke workflow runs no consumer-defined script: it installs and activates the
built artifact through wp-env.
