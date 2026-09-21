# a8csp-configs

`a8csp-configs` provides shared PHP quality-assurance configs under `php/`, Node.js tool base configs under `node/`, and reusable GitHub Actions workflows for A8C Special Projects repositories.

## `php/` configs

The PHP quality-assurance configs live in `php/quality-assurance/`:

| File | Purpose |
| --- | --- |
| `phpcs.dist.xml` | Production profile: the shared base plus the `tests/` exclusion and production-only settings. |
| `phpcs.tests.dist.xml` | Companion ruleset for `tests/`: the shared base plus test-only relaxations, referenced by a consumer's own tests-only ruleset the same way `phpcs.dist.xml` is referenced by its main ruleset. |
| `phpcs.base.dist.xml` | The rules both profiles include: PHPCompatibilityWP, WordPress-Extra, and WordPress-Docs, the supported-version floors, scan exclusions, and project-wide PHPCS settings. Consumers reference the two profiles, not this file. |
| `phpstan.dist.neon` | Defines the PHPStan level, shared type aliases, and strict-rule settings. The WordPress stubs load through the phpstan-wordpress extension. |
| `phpstan.dist.neon.php` | Adds the conventional root files (`functions-bootstrap.php`, `functions.php`, `uninstall.php`) and source directories to the analysed paths when they exist, and adds scoped dependencies for scanning. It does not add the plugin entry file. |

The package is not published on Packagist; it resolves from its GitHub repository through the `repositories` entry.

Require an immutable release tag in the consuming project's `composer.json`:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/a8cteam51/a8csp-configs" }
  ],
  "require-dev": {
    "a8csp/configs": "v1.0.0",
    "roave/security-advisories": "dev-latest",
    "phpcompatibility/phpcompatibility-wp": "^3@alpha",
    "phpcompatibility/php-compatibility": "^10@alpha",
    "phpcompatibility/phpcompatibility-paragonie": "^2@alpha"
  }
}
```

The `roave/security-advisories` line is mandatory. This package requires `roave/security-advisories` so that no consumer can install a dependency version with a known security advisory. That package has no stable release, and Composer honors stability flags only in the root package, so under the default `minimum-stability` of `stable`, Composer refuses to install `a8csp/configs` without the root line.

The three `phpcompatibility/*` lines are mandatory as well. The shared ruleset runs PHPCompatibilityWP, but this package deliberately does not pin those packages, because only the consumer can select the pre-release majors that sniff current PHP syntax. Without those root-side requirements Composer resolves the stable releases, whose sniffs do not cover current PHP syntax — the `testVersion` checks then pass vacuously.

Reference the shared PHPCS ruleset from the consumer's `.phpcs.xml`:

```xml
<?xml version="1.0"?>
<ruleset name="Project">
    <rule ref="vendor/a8csp/configs/php/quality-assurance/phpcs.dist.xml"/>
</ruleset>
```

Include the shared PHPStan config from the consumer's `.phpstan.neon`:

```neon
includes:
    - vendor/a8csp/configs/php/quality-assurance/phpstan.dist.neon
```

The shared PHPStan config includes `phpstan.dist.neon.php` automatically. The consumer declares `parameters.paths` for its plugin entry file (and any source directory outside the conventional set) and `WPCompat.pluginFile` — or `requiresAtLeast` — explicitly in its own config; this package contributes the analysis rules, WordPress stubs (through its phpstan-wordpress dependency), scoped-dependency scanning, and detection of conventional root files and source directories.

## `node/` configs

Install the package as an npm Git dependency. Use a release tag for normal consumption or a full commit SHA when testing an exact revision:

```json
{
  "devDependencies": {
    "@a8csp/configs": "github:a8cteam51/a8csp-configs#<tag-or-sha>"
  }
}
```

The `package.json` exports map exposes these paths:

| Export path | File | Purpose |
| --- | --- | --- |
| `@a8csp/configs/node/eslint.config.base.mjs` | `node/eslint.config.base.mjs` | Flat ESLint baseline using the WordPress recommended, unit-test, and Playwright configurations. |
| `@a8csp/configs/node/stylelint.config.base.js` | `node/stylelint.config.base.js` | Stylelint baseline extending the WordPress SCSS config, defining shared ignored files, and reporting needless, invalid-scope, and descriptionless disable comments. |
| `@a8csp/configs/node/postcss.config.base.js` | `node/postcss.config.base.js` | PostCSS baseline wiring the WordPress plugin preset for compiled-CSS post-processing. |
| `@a8csp/configs/node/playwright.config.base.js` | `node/playwright.config.base.js` | Playwright baseline factory. Called with `{ port }` — the port the consumer's `.wp-env.json` serves on — it returns the WordPress Scripts config with `tests/EndToEnd` as the test directory, the wp-env base URL and artifacts path set, and `npm run wp-env:start` as the web-server command. |
| `@a8csp/configs/node/tsconfig.base.json` | `node/tsconfig.base.json` | TypeScript baseline for JSX, isolated modules, JSON modules, control-flow checks, and no-emit type checking. |

Import or require the JavaScript configs by their export paths, and use the TypeScript export path in a consumer config's `extends` field.

## Reusable workflows

The reusable workflows cover block metadata validation, CodeQL, PHP lint scripts, PHP syntax, PHPUnit, Playwright end-to-end tests, plugin releases, script and style linting, supply-chain audits, and workflow checks. The release workflow is plugin-only. See [the reusable-workflow reference](docs/workflows.md) for every input and behavior note. See [the scripts contract](docs/scripts-contract.md) for the composer/npm script names these workflows expect a consumer to define.

A caller references a workflow from its own workflow file and pins the reference to a tag:

```yaml
jobs:
  phpunit:
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-phpunit.yml@v1.0.0
```

## Supported floors

- PHP 8.5 or later
- WordPress 7.1 or later

## Versioning

Consumers should pin Composer requirements and reusable-workflow `uses:` references to immutable semver tags in `vX.Y.Z` form. `trunk` is the development branch and may break between tags. A full commit SHA is also an acceptable immutable pin for a reusable-workflow `uses:` reference.

Coming from the legacy package? See [the migration guide](docs/migrating-from-team51-configs.md).
