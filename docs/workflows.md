# Reusable workflows

This document is the per-workflow `workflow_call` input reference for the reusable workflows in this repository. The [backwards-compatibility rules](../CONTRIBUTING.md#backwards-compatibility-contract) keep existing inputs stable until a major release, except that a [tool runtime move](../CONTRIBUTING.md#tool-runtimes) may raise a version input's default in a minor release; additional inputs must be optional and define a default.

## block.json Schema Check — `.github/workflows/reusable-block-json-check.yml`

Finds `block.json` files under the project path and validates each one against the WordPress `block.json` schema from `schemas.wp.org`.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. |

The workflow exits successfully with a message when it finds no `block.json` files. The search prunes `./node_modules` and `./vendor` under `project-path`.

```yaml
jobs:
  block-json:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-block-json-check.yml@vX.Y.Z
```

## CodeQL — `.github/workflows/reusable-codeql.yml`

Runs GitHub CodeQL initialization and analysis as one matrix job per configured language.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `languages` | `string` | No | `'["actions"]'` | Non-empty JSON array of CodeQL languages. |

An empty `languages` array fails validation. CodeQL has no PHP analyzer; PHPStan covers PHP.

A `detect-ghas` job probes `code-scanning/alerts` first. Without GitHub Advanced Security, the usual state of a private repository, `analyze` is skipped with a notice; a public repository always has it.

```yaml
jobs:
  codeql:
    permissions:
      actions: read
      contents: read
      security-events: write
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-codeql.yml@vX.Y.Z
    with:
      languages: '["actions", "javascript-typescript"]'
```

## PHP Lint — `.github/workflows/reusable-php-lint.yml`

Validates `composer.json` and its lock, installs Composer dependencies, and runs each configured Composer script as a separate matrix job.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `scripts` | `string` | Yes | — | Non-empty JSON array of Composer scripts; each script runs as a parallel job. |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. |

An empty `scripts` array fails validation. Each matrix job invokes its script as `composer "$SCRIPT"` on PHP 8.5. `composer validate --strict` always runs as its own job: it fails when `composer.lock` is out of date with `composer.json` or `composer validate` reports any warning, which the dependency install only warns about.

```yaml
jobs:
  php-lint:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-php-lint.yml@vX.Y.Z
    with:
      scripts: '["lint:php:phpcs", "lint:php:phpcs:tests", "lint:php:phpstan"]'
```

## PHP Syntax Check — `.github/workflows/reusable-php-syntax-check.yml`

Runs `php -l` over every PHP file under the project path for each configured PHP version, without installing dependencies or loading an autoloader.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. |
| `php-versions` | `string` | No | `'["8.5","8.6"]'` | Non-empty JSON array of PHP versions to check. |

An empty `php-versions` array fails validation. The file search prunes `./vendor` and `./node_modules` under `project-path`.

```yaml
jobs:
  php-syntax:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-php-syntax-check.yml@vX.Y.Z
```

## PHPUnit — `.github/workflows/reusable-phpunit.yml`

Installs Composer dependencies, optionally starts a WordPress environment, runs a Composer test script, and stops the environment after the test run.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. |
| `php-version` | `string` | No | `'8.5'` | PHP version on the runner, which runs Composer and any suite that does not use wp-env. Suites inside wp-env run on the `phpVersion` of its config file. |
| `wp-version` | `string` | No | `''` | WordPress release to run, as a `WordPress/WordPress` tag name. An empty value defers to the `core` setting in the project's wp-env config file, or wp-env's default, the latest stable release. |
| `wp-env-core` | `string` | No | `''` | Full `WP_ENV_CORE` value. Overrides `wp-version` and accepts repository refs or ZIP URLs. |
| `composer-script` | `string` | No | `'test'` | Composer script invoked for the test run. |
| `wp-env-config-file` | `string` | No | `''` | wp-env configuration path relative to `project-path`. An empty value uses `.wp-env.json`; use a separate file per environment instead of the deprecated implicit development/test split. |
| `wp-env-xdebug` | `string` | No | `''` | Value passed to `wp-env start --xdebug=<mode>`, such as `coverage`. An empty value starts without Xdebug. |
| `needs-wp-env` | `boolean` | No | `true` | Whether to run `npm ci` and start and stop wp-env. A caller with a unit-only suite must pass `false`; left at the default, the run fails unless the project declares `@wordpress/env`. |

When `needs-wp-env` is `true`, the project must contain `package.json` and `package-lock.json` declaring `@wordpress/env`: the workflow starts the project's own locked wp-env and fails with a named error when it is missing. `wp-env-core` takes precedence over `wp-version`, and the stop step runs under `always()`.

The resulting `WP_ENV_CORE` is set for the whole job, so a Composer script that starts wp-env itself gets the same WordPress as the workflow's own start. It is the only one of the workflow's settings that a restarted or re-invoked wp-env inherits: wp-env reads no environment variable for its config file or Xdebug mode, so a Composer script that calls wp-env must pass the same `--config <file>` on every call, and the same `--xdebug=<mode>` whenever it restarts wp-env. A call without `--config` addresses a different environment that was never started, and a restart without `--xdebug` rebuilds the containers without Xdebug.

```yaml
jobs:
  phpunit:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-phpunit.yml@vX.Y.Z
```

## Playwright E2E — `.github/workflows/reusable-playwright-e2e.yml`

Installs PHP and Node.js dependencies, starts the project's `.wp-env.json` environment, runs `npm run test:e2e`, and uploads a failure report.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. `npm ci` requires `package.json` and `package-lock.json` there, declaring `@wordpress/env` — the workflow starts the environment with the project's own locked binary. |
| `wp-version` | `string` | No | `''` | WordPress release to run, as a `WordPress/WordPress` tag name. An empty value defers to the `core` setting in the project's `.wp-env.json`, or wp-env's default, the latest stable release. |
| `wp-env-core` | `string` | No | `''` | Full `WP_ENV_CORE` value. Overrides `wp-version` and accepts repository refs or ZIP URLs. |

The resulting `WP_ENV_CORE` is set for the whole job, as in the PHPUnit workflow. Composer dependencies are installed with development packages included. The workflow runs no build, so the suite exercises the build output committed at the tested commit. It installs and caches Chromium, stops wp-env under `always()`, and uploads the `playwright-report` artifact on failure.

```yaml
jobs:
  playwright:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-playwright-e2e.yml@vX.Y.Z
```

## Release — `.github/workflows/reusable-release.yml`

Verifies, builds, smoke-tests, and publishes a plugin release as a GitHub release carrying the plugin zip. It is plugin-only: it reads the plugin header's `Version`, generates the translation template in plugin mode, archives with `wp dist-archive --plugin-dirname`, and smoke-tests the zip by activating it as a plugin, so a site or theme repository cannot use it.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `plugin-slug` | `string` | Yes | — | Plugin directory slug. Names the archive, the POT, and the directory the zip unpacks to. |
| `php-version` | `string` | Yes | — | PHP version the build and the smoke environment run. Must satisfy the plugin header's `Requires PHP`, or WordPress refuses to activate the artifact. |
| `entry-file` | `string` | No | `''` | Main plugin file carrying the `Version` header. An empty value uses `<plugin-slug>.php`. |
| `extra-plugins` | `string` | No | `'[]'` | JSON array of additional wp-env plugin sources installed alongside the artifact during the smoke test. |
| `extra-smoke-commands` | `string` | No | `'[]'` | JSON array of wp-cli argument strings run as additional smoke assertions. |
| `publish` | `boolean` | No | `true` | Whether to create the GitHub release. `true` requires a tag run; on any other ref the version check fails. `false` runs every step before it, which exercises the release path without a tag. |

The version check runs first: the plugin header `Version`, the `package.json` `version`, and the first `## ` heading of `CHANGELOG.md` must agree, and on a tag run they must also equal the tag without its `v` prefix. Two branches then run in parallel, and the release publishes only when both succeed:

- **Provenance.** The tagged commit must already have successful `push` runs of `.github/workflows/quality.yml` and `.github/workflows/tests.yml`. A release reuses those results instead of running the suites again, so tag only a commit whose Quality and Tests runs are green.
- **Build and smoke test.** The build runs `composer changelog:validate`, installs production Composer dependencies, regenerates the POT, and creates the zip. It runs no npm command, so the zip carries the build output committed at the tagged commit. The smoke job then installs and activates the zip, together with `extra-plugins`, in a fresh wp-env running `php-version`, fails if activation errors or the site stops serving, and runs each `extra-smoke-commands` entry: everything after `wp`, split into arguments on whitespace, where a non-zero exit fails the job. The artifact differs materially from the tested tree (production dependencies, a regenerated POT, `.distignore` filtering), so it is exercised once before publishing.

Publishing creates the GitHub release with the zip attached and the version's `CHANGELOG.md` section as its notes. A tag containing `-` is published as a prerelease and is not marked as the latest release.

[The scripts contract](scripts-contract.md#release) lists the files and scripts this workflow expects the consumer to provide. The caller must grant `contents: write` and `actions: read`.

The smoke job names a wp-env version of its own, `WP_ENV_VERSION` in the job's `env`. It runs against a downloaded artifact in a directory with no checkout and no `package.json`, so there is no locked binary to defer to the way the PHPUnit and Playwright workflows do.

```yaml
on:
  push:
    tags: ['v*']

jobs:
  release:
    permissions:
      actions: read
      contents: write
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-release.yml@vX.Y.Z
    with:
      plugin-slug: ${{ github.event.repository.name }}
      php-version: '8.5'
```

## Scripts/Styles Lint — `.github/workflows/reusable-scripts-styles-lint.yml`

Installs npm dependencies, then runs `npm run lint:scripts` and `npm run lint:styles`, and `npm run lint:types` when `lint-types` is `true`.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. `npm ci` requires `package.json` and `package-lock.json` there. |
| `lint-types` | `boolean` | No | `false` | Whether to run `npm run lint:types` with the TypeScript compiler. Set `true` for repositories with TypeScript sources. |

```yaml
jobs:
  scripts-styles:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-scripts-styles-lint.yml@vX.Y.Z
```

## Supply-Chain Audit — `.github/workflows/reusable-supply-chain-audit.yml`

Audits the committed `composer.lock` and `package-lock.json` in two parallel jobs that always run, without installing anything, so the audit never executes the dependencies it vets.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `project-path` | `string` | No | `'.'` | Path to the project, relative to the repository root. Both lock files must be present there. |

- `composer audit --locked --abandoned=report` audits the full Composer graph. It fails on any security advisory and reports abandoned packages without failing.
- `npm audit --audit-level=high --omit=dev` audits the production npm graph. It fails on high or critical advisories and reports low and moderate ones without failing.

```yaml
jobs:
  supply-chain:
    permissions:
      contents: read
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-supply-chain-audit.yml@vX.Y.Z
```

## Workflow Checks — `.github/workflows/reusable-workflow-checks.yml`

Runs Actionlint and Zizmor static analysis against `.github/workflows/**`.

| Input | Type | Required | Default | Description |
| --- | --- | --- | --- | --- |
| `zizmor-inputs` | `string` | No | `'.'` | The path zizmor audits. Narrow it for consumers that must exclude vendored third-party workflow files. |

The caller grants `contents: read`, `security-events: write`, and `actions: read`, as shown below; a caller must grant every permission the called jobs declare, or the call fails at startup.

A `detect-ghas` probe picks the zizmor mode; actionlint and zizmor always run. With GitHub Advanced Security, zizmor uploads SARIF to the Security tab and a finding-count gate fails the job; without it, zizmor runs in native mode and fails the job itself, with inline annotations. Both modes fail on any finding.

```yaml
jobs:
  workflow-checks:
    permissions:
      actions: read
      contents: read
      security-events: write
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-workflow-checks.yml@vX.Y.Z
```
