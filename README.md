# a8csp-configs

`a8csp-configs` provides four shared PHP quality-assurance configs under `php/`, five Node.js tool base configs under `node/`, and nine reusable GitHub Actions workflows for A8C Special Projects repositories.

## `php/` configs

The PHP quality-assurance configs live in `php/quality-assurance/`:

| File | Purpose |
| --- | --- |
| `phpcs.dist.xml` | Defines the PHPCompatibilityWP, WordPress-Extra, and WordPress-Docs rules, supported-version checks, scan exclusions, and project-wide PHPCS settings. |
| `phpcs.tests.dist.xml` | Companion ruleset for `tests/`: a distinct rule profile for test code, referenced by a consumer's own tests-only ruleset the same way `phpcs.dist.xml` is referenced by its main ruleset. |
| `phpstan.dist.neon` | Defines the PHPStan level, WordPress stubs, shared type aliases, and strict-rule settings. |
| `phpstan.dist.neon.php` | Discovers conventional plugin entry points and source directories, and adds scoped dependencies for scanning. |

The package is not published on Packagist; it resolves from its GitHub repository through the `repositories` entry.

Require an immutable release tag in the consuming project's `composer.json`:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/a8cteam51/a8csp-configs" }
  ],
  "require-dev": {
    "a8csp/configs": "v1.0.0"
  }
}
```

Reference the shared PHPCS ruleset from the consumer's `phpcs.xml.dist`:

```xml
<?xml version="1.0"?>
<ruleset name="Project">
    <rule ref="vendor/a8csp/configs/php/quality-assurance/phpcs.dist.xml"/>
</ruleset>
```

Include the shared PHPStan config from the consumer's `phpstan.neon.dist`:

```neon
includes:
    - vendor/a8csp/configs/php/quality-assurance/phpstan.dist.neon
```

The shared PHPStan config includes `phpstan.dist.neon.php` automatically. The consumer declares `parameters.paths` for its plugin entry file (and any source directory outside the conventional set) and `WPCompat.pluginFile` — or `requiresAtLeast` — explicitly in its own config; the shared configuration contributes the analysis rules, WordPress stubs, scoped-dependency scanning, and detection of conventional root files and source directories.

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
| `@a8csp/configs/node/stylelint.config.base.js` | `node/stylelint.config.base.js` | Stylelint baseline extending the WordPress SCSS config and defining shared ignored files. |
| `@a8csp/configs/node/postcss.config.base.js` | `node/postcss.config.base.js` | PostCSS baseline wiring the WordPress plugin preset (and Sass parsing) for CSS/SCSS builds. |
| `@a8csp/configs/node/playwright.config.base.js` | `node/playwright.config.base.js` | Playwright baseline extending the WordPress Scripts config and using `tests/EndToEnd` as the test directory. |
| `@a8csp/configs/node/tsconfig.base.json` | `node/tsconfig.base.json` | TypeScript baseline for JSX, isolated modules, JSON modules, control-flow checks, and no-emit type checking. |

Import or require the JavaScript configs by their export paths, and use the TypeScript export path in a consumer config's `extends` field.

## Reusable workflows

The nine reusable workflows cover block metadata validation, CodeQL, PHP lint scripts, PHP syntax, PHPUnit, Playwright end-to-end tests, script and style linting, supply-chain audits, and workflow checks. See [the reusable-workflow reference](docs/workflows.md) for every input and behavior note. See [the scripts contract](docs/scripts-contract.md) for the composer/npm script names these workflows expect a consumer to define.

A caller references a workflow from its own workflow file and pins the reference to a tag:

```yaml
jobs:
  phpunit:
    uses: a8cteam51/a8csp-configs/.github/workflows/reusable-phpunit.yml@v1.0.0
```

## Supported floors

- PHP 8.5 or later
- WordPress 7.0 or later

## Versioning

Consumers should pin Composer requirements and reusable-workflow `uses:` references to immutable semver tags in `vX.Y.Z` form. `trunk` is the development branch and may break between tags. A full commit SHA is also an acceptable immutable pin for a reusable-workflow `uses:` reference.

Coming from the legacy package? See [the migration guide](docs/migrating-from-team51-configs.md).
