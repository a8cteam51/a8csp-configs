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
    "a8csp/configs": "v1.0.0"
  }
}
```

Use the tag selected for the migration rather than `trunk`.

The shared ruleset runs PHPCompatibilityWP, but this package deliberately does not pin the PHPCompatibility packages: Composer honors stability flags only in the root package, so the pre-release majors that sniff current PHP syntax can only be selected by the consumer. Require them alongside the package:

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
