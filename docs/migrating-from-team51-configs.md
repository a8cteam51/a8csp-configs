# Migrating from `a8cteam51/team51-configs`

This guide is for a consumer that currently requires `a8cteam51/team51-configs` and is switching to `a8csp/configs`.

## When to migrate

`a8cteam51/team51-configs` is frozen: it receives no further changes, and a project that requires it keeps working as it is. Migrate a project when it next gets real work.

A migrated project can keep its own PHP and WordPress floors, as [Version floors](#version-floors) describes. Its development and CI tooling, however, run on the PHP version this package requires (see [Supported floors](../README.md#supported-floors)).

## Composer package

Replace the `a8cteam51/team51-configs` entry in the appropriate `composer.json` requirement section with `a8csp/configs` at the current major.

Update the existing `repositories` VCS entry, or add one, to point at `https://github.com/a8cteam51/a8csp-configs` instead of the legacy `team51-configs` repository:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/a8cteam51/a8csp-configs" }
  ],
  "require-dev": {
    "a8csp/configs": "^1",
    "roave/security-advisories": "dev-latest"
  },
  "config": {
    "allow-plugins": {
      "dealerdirect/phpcodesniffer-composer-installer": true,
      "phpstan/extension-installer": true
    }
  }
}
```

Use the tag selected for the migration rather than `trunk`.

Composer honors stability flags only in the root package, and two root requirements follow from that.

The `roave/security-advisories` line above is mandatory. This package requires `roave/security-advisories` so that no consumer can install a dependency version with a known security advisory, and because that package has no stable release, Composer refuses to install `a8csp/configs` without the root line under the default `minimum-stability` of `stable`.

The shared ruleset runs PHPCompatibilityWP, and this package accepts either the stable or the pre-release majors of the PHPCompatibility packages, because only the consumer's root requirements can select the pre-release majors that sniff current PHP syntax. Require them alongside the package:

```json
{
  "require-dev": {
    "phpcompatibility/phpcompatibility-wp": "^3@alpha",
    "phpcompatibility/php-compatibility": "^10@alpha",
    "phpcompatibility/phpcompatibility-paragonie": "^2@alpha"
  }
}
```

Without these root-side requirements, Composer resolves the stable releases, whose sniffs do not cover current PHP syntax — `testVersion`-based checks then pass vacuously.

Keep both `allow-plugins` entries, which a project that required `a8cteam51/team51-configs` usually has already: Composer honors them only in the root package and aborts a non-interactive install on any plugin they do not allow, and these two plugins register the PHPCS standards the shared rulesets name and load the PHPStan extensions, including the WordPress stubs.

## Ruleset paths

Point the consumer's PHPCS ruleset at:

```xml
<rule ref="vendor/a8csp/configs/php/quality-assurance/phpcs.dist.xml" />
```

That production profile excludes `tests/`. Lint test code with a second, tests-only ruleset that points at the companion profile, and run it as its own PHPCS invocation (the `lint:php:phpcs:tests` script in [the scripts contract](scripts-contract.md)); without it, test code goes unchecked:

```xml
<rule ref="vendor/a8csp/configs/php/quality-assurance/phpcs.tests.dist.xml" />
```

Point the consumer's PHPStan configuration at:

```neon
includes:
    - %currentWorkingDirectory%/vendor/a8csp/configs/php/quality-assurance/phpstan.dist.neon
```

These are the canonical ruleset paths in this package. It has no legacy `quality-assurance/` shim path, unlike `a8cteam51/team51-configs`.

The `%currentWorkingDirectory%` anchor keeps the include valid from a config in a subdirectory, such as a site repository's per-theme `.phpstan.neon`: PHPStan resolves a bare relative include against the including file's directory, and the shared config expects PHPStan to run from the repository root.

## PHPStan entry file

The shared PHPStan configuration adds the conventional root files (`functions-bootstrap.php`, `functions.php`, `uninstall.php`) and source directories (`src`, `includes`, `models`, `blocks`, `templates`) to the analyzed paths when they exist. It does not add the plugin entry file, whose name differs per repository, so PHPStan never analyzes that file unless the consumer lists it. List it under `parameters.paths` in the consumer's PHPStan configuration, together with any other file or directory outside the conventional set, and name it as `WPCompat.pluginFile` too. The WordPress compatibility rules read the plugin's `Requires at least` header from that file; without it they look for a file named after the checkout directory, then `plugin.php`, then `style.css`, and stop with "No plugin or theme file found" when none exists. Setting `WPCompat.requiresAtLeast` to a version instead also works.

```neon
parameters:
    paths:
        - %currentWorkingDirectory%/my-plugin.php
    WPCompat:
        pluginFile: %currentWorkingDirectory%/my-plugin.php
```

## Version floors

Migration raises the configured floors to the [supported floors](../README.md#supported-floors) through the `testVersion` and `minimum_wp_version` values in `php/quality-assurance/phpcs.base.dist.xml`, which both the production and tests rulesets include.

A consumer that is not ready for either floor opts out per repository as [CONTRIBUTING](../CONTRIBUTING.md#version-floors) describes.

## PHPMD

PHPMD is not part of this package, which ships no PHPMD ruleset. A consumer that still uses PHPMD requires `phpmd/phpmd` directly, copies `quality-assurance/phpmd.dist.xml` from `a8cteam51/team51-configs` into its own repository, and points the `<rule ref>` in its `.phpmd.xml` at that copy. It cannot keep requiring `a8cteam51/team51-configs` for the ruleset: that package and this one require different majors of `johnbillion/wp-compat`, so Composer cannot install both.

## Reusable workflows

Migrating to `a8csp-configs` adds access to reusable workflows, which `team51-configs` did not provide. Add a `uses:` reference to the consumer's own workflow files for each reusable workflow it needs:

```yaml
uses: a8cteam51/a8csp-configs/.github/workflows/<workflow-file>.yml@<release-commit-sha> # vX.Y.Z
```

Do not reference `trunk` from a production consumer. Pin each call by the release's full commit SHA, with the tag as a comment, as [the workflow reference](workflows.md) explains.

See [docs/workflows.md](workflows.md) for the full per-workflow reference.
