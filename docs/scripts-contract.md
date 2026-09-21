# Scripts contract

This document names the composer and npm script identifiers the reusable workflows in this
repository treat as their interface. A consuming project's `composer.json` and `package.json`
scripts must use these names for the corresponding reusable workflow to find and run them. It also
lists the files the release workflow reads from the consumer's tree, the names the fleet shares by
convention alone, what a consumer's own scripts have to do for the workflows to hold, and the
PHPUnit configuration every repository copies.

## Fixed names

These names are hardcoded in the reusable workflow and are not configurable via a `workflow_call`
input.

| Script | Ecosystem | Workflow | Condition |
| --- | --- | --- | --- |
| `lint:scripts` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-scripts` input is `true` (default). |
| `lint:styles` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-styles` input is `true` (default). |
| `lint:types` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-types` input is `true`; the input defaults to `false`, so a consumer with TypeScript sources opts in. |
| `changelog:validate` | composer | `reusable-release.yml` | Runs in every release build, before the production install. |
| `test:e2e` | npm | `reusable-playwright-e2e.yml` | Runs the Playwright suite once wp-env is up. |

## Default names (input-overridable)

These names are the default value of a `workflow_call` input; a caller may override them, but
matching the default avoids an unnecessary `with:` entry.

| Script | Ecosystem | Workflow | Input |
| --- | --- | --- | --- |
| `test` | composer | `reusable-phpunit.yml` | `composer-script` |

## Convention (consumer-declared, not enforced by the workflow)

`reusable-php-lint.yml` takes a required `scripts` array of composer script names and runs each as
its own matrix job; the workflow itself names none of them. The established convention across
consumers is a `lint:php` composer script that aggregates `lint:php:phpcs`, `lint:php:phpcs:tests`,
and `lint:php:phpstan`, passed as `'["lint:php:phpcs", "lint:php:phpcs:tests", "lint:php:phpstan"]'`.
The `lint:php:phpcs:tests` job runs the companion tests-profile ruleset over `tests/`; a consumer
that omits it lints production code but leaves its test code unchecked.

## Conventional names (not read by any workflow)

No workflow resolves any of these. They are the names the fleet has settled on for recurring jobs,
so that a developer moving between repositories reaches for the same script. A repository that has
the job uses the name; one that does not have the job omits the script.

| Script | Ecosystem | Job |
| --- | --- | --- |
| `packages-install` | composer, npm | Installs dependencies for local work. The composer copy passes `--ignore-platform-req=php+`, so a PHP newer than the declared floor still installs. |
| `packages-update` | composer, npm | Updates dependencies within the declared constraints. |
| `packages-update:wp` | npm | Moves the `@wordpress/*` packages onto the dist-tag for the supported WordPress version. |
| `audit` | npm | Runs the Composer and npm audits together, with the flags `reusable-supply-chain-audit.yml` uses, so a local run and CI agree. |
| `check:engines` | npm | Checks the running Node and npm against the `engines` field. |
| `check:licenses` | npm | Checks dependency licenses. |
| `format:php` | composer | Rewrites PHP with `phpcbf` against the repository's own ruleset. |
| `internationalize` | composer | Runs the `i18n:*` steps in order to regenerate the language files. |
| `quality-check` | composer | The aggregate to run before pushing: the lint scripts plus the unit suite. |
| `changelog:add` | composer | Records a changelog entry for the change in hand. |
| `changelog:write` | composer | Folds the recorded entries into `CHANGELOG.md` for a release. |
| `wp-env:start` | npm | Starts the development environment. This one name is load-bearing — see [Consumer obligations](#consumer-obligations). |

Nothing fails when a name here is missing or spelled differently. The names under Fixed names and
Default names are the ones a workflow resolves.

## Consumer obligations

### The wp-env start script must be named `wp-env:start`

`node/playwright.config.base.js` sets `webServer.command` to `npm run wp-env:start`, in place of
the `npm run wp-env start` default in the `@wordpress/scripts` Playwright base. A consumer that
names the script anything else has no web server at all. This one fails loudly on the first local
`npm run test:e2e` and never surfaces in CI, because `reusable-playwright-e2e.yml` starts wp-env in
its own step and Playwright's `reuseExistingServer` finds it already listening.

## PHPUnit configuration

PHPUnit's XML format has no include mechanism, so this package ships no shared base and every
repository owns its configuration outright. The convention below is what keeps those copies from
drifting.

The file is named `phpunit.dist.xml` and carries at least these attributes on the root element:

| Attribute | Value | Purpose |
| --- | --- | --- |
| `failOnWarning` | `true` | A warning ends the run rather than scrolling past in a green report. |
| `failOnRisky` | `true` | A test that asserts nothing or leaves state behind ends the run. |
| `failOnNotice` | `true` | A notice ends the run. |
| `beStrictAboutOutputDuringTests` | `true` | Output from the code under test marks the test risky, which `failOnRisky` then turns into a failure. |
| `colors` | `true` | Readable output locally and in the Actions log. |
| `cacheDirectory` | `tests/.cache/phpunit` | Keeps the result cache beside the other tool caches instead of in the repository root. |

`executionOrder` is `depends,defects,random`. A repository that needs a different order states the
reason in a comment beside the attribute. `resolveDependencies` is a separate attribute defaulting
to `true`, so `random` still honors `#[Depends]`; running defects first keeps the local feedback
loop short, and randomizing the rest surfaces tests that pass only in a particular order.

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
