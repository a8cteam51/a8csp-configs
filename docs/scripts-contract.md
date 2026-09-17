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
| `lint:types` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-types` input is `true`; the input defaults to `false`, so a consumer with TypeScript sources opts in. |
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

## Consumer obligations

These are requirements on what a consumer's scripts do, not on what they are called. No workflow
input controls either one, and the two fail in opposite ways.

### `test:integration` must not start wp-env when `$GITHUB_ACTIONS` is set

`reusable-phpunit.yml` sets `WP_ENV_CORE` in the `env:` block of its own "Start wp-env" step and
invokes the consumer's composer script from a separate "Run tests" step, where the variable is
unset. A `test:integration` script that starts wp-env itself therefore starts it without the
version the caller selected: wp-env falls back to the `core` value in the consumer's config file,
the suite runs against that WordPress instead, and the matrix leg reports success for a version it
never tested. Guard the start on `$GITHUB_ACTIONS` so the script provisions an environment locally
and uses the one the workflow already started in CI.

### The wp-env start script must be named `wp-env:start`

`node/playwright.config.base.js` sets `webServer.command` to `npm run wp-env:start`, in place of
the `npm run wp-env start` default in the `@wordpress/scripts` Playwright base. A consumer that
names the script anything else has no web server at all. This one fails loudly on the first local
`npm run test:e2e` and never surfaces in CI, because `reusable-playwright-e2e.yml` starts wp-env in
its own step and Playwright's `reuseExistingServer` finds it already listening.

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
