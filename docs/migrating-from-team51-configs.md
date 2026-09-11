# Migrating from `a8cteam51/team51-configs`

This guide is for a consumer that currently requires `a8cteam51/team51-configs` and is switching to `a8csp/configs`.

## Composer package

Replace the `a8cteam51/team51-configs` entry in the appropriate `composer.json` requirement section with `a8csp/configs`, pinned to an immutable semantic-version tag.

Update the existing `repositories` VCS entry, or add one, to point at `https://github.com/a8cteam51/a8csp-configs` instead of the legacy `team51-configs` repository:

```json
{
  "repositories": [
    { "type": "vcs", "url": "https://github.com/a8cteam51/a8csp-configs" }
  ],
  "require-dev": {
    "a8csp/configs": "v1.0.0",
    "roave/security-advisories": "dev-latest"
  }
}
```

Use the tag selected for the migration rather than `trunk`.

Composer honors stability flags only in the root package, and two root requirements follow from that.

The `roave/security-advisories` line above is mandatory. This package requires `roave/security-advisories` so that no consumer can install a dependency version with a known security advisory, and because that package has no stable release, Composer refuses to install `a8csp/configs` without the root line under the default `minimum-stability` of `stable`.

The shared ruleset runs PHPCompatibilityWP, but this package deliberately does not pin the PHPCompatibility packages, because only the consumer can select the pre-release majors that sniff current PHP syntax. Require them alongside the package:

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

## Ruleset paths

Point the consumer's PHPCS ruleset at:

```xml
<rule ref="vendor/a8csp/configs/php/quality-assurance/phpcs.dist.xml" />
```

Point the consumer's PHPStan configuration at:

```neon
includes:
    - vendor/a8csp/configs/php/quality-assurance/phpstan.dist.neon
```

These are the canonical and only ruleset paths in this package. It has no legacy `quality-assurance/` shim path, unlike `a8cteam51/team51-configs`.

## PHPStan entry file

The shared PHPStan configuration adds the conventional root files (`functions-bootstrap.php`, `functions.php`, `uninstall.php`) and source directories (`src`, `includes`, `models`, `blocks`, `templates`) to the analysed paths when they exist. It does not add the plugin entry file, whose name differs per repository, so PHPStan never analyses that file unless the consumer lists it. List it under `parameters.paths` in the consumer's PHPStan configuration, together with any other file or directory outside the conventional set, and name it as `WPCompat.pluginFile` too. The WordPress compatibility rules read the plugin's `Requires at least` header from that file; without it they look for a file named after the checkout directory, then `plugin.php`, then `style.css`, and stop with "No plugin or theme file found" when none exists. Setting `WPCompat.requiresAtLeast` to a version instead also works.

```neon
parameters:
    paths:
        - %currentWorkingDirectory%/my-plugin.php
    WPCompat:
        pluginFile: %currentWorkingDirectory%/my-plugin.php
```

## Version floors

Migration raises the configured floors to PHP 8.5 and WordPress 7.1 through the `testVersion` and `minimum_wp_version` values in `php/quality-assurance/phpcs.dist.xml` and its tests companion, `phpcs.tests.dist.xml`.

A consumer that is not ready for either floor must opt out per repository with `--runtime-set` in its own PHPCS CLI invocation. A consuming ruleset's `<config>` elements cannot override an included ruleset's already-set `<config>` values, regardless of declaration order. Only `--runtime-set` has precedence over ruleset-level configuration.

The consumer sets `testVersion` and `minimum_wp_version` with `--runtime-set` on its own `phpcs` invocation, typically in its `composer.json` lint script.

## PHPMD

PHPMD is not part of this package. A consumer that still uses PHPMD must keep requiring `a8cteam51/team51-configs` or vendor `phpmd/phpmd` directly for that dependency. This package ships no PHPMD ruleset.

## Reusable workflows

Migrating to `a8csp-configs` adds access to reusable workflows, which `team51-configs` did not provide. Add a `uses:` reference to the consumer's own workflow files for each reusable workflow it needs:

```yaml
uses: a8cteam51/a8csp-configs/.github/workflows/<workflow-file>.yml@v1.0.0
```

Do not reference `trunk` from a production consumer.

See [docs/workflows.md](workflows.md) for the full per-workflow reference.
