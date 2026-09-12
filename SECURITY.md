# Security Policy

## Reporting a vulnerability

Use GitHub private vulnerability reporting for `a8cteam51/a8csp-configs`. Open the repository's **Security** tab, select **Report a vulnerability**, and submit the report there. Do not disclose vulnerability details in a public issue.

## Consumption contract

Consumers must pin Composer requirements and reusable-workflow `uses:` references to immutable semantic-version tags (`vX.Y.Z`) or full commit SHAs. `trunk` is the development branch and may break between tags; production consumers must not resolve `trunk`.

A tagged release must be additive or strictly permissive relative to the preceding tag. Permitted changes include adding a file, adding an optional input with a default, and loosening a rule. A tagged release must not remove or rename an input or tighten a rule without an escape hatch.

The one sanctioned exception is a change to the PHP or WordPress version floors enforced by the `testVersion` and `minimum_wp_version` values in `php/quality-assurance/phpcs.base.dist.xml`, which both PHPCS rulesets include. PHPCS fixes an included ruleset's `<config>` values when it loads them, so a consuming ruleset cannot override those values with its own `<config>` elements, regardless of declaration order. A consumer that is not ready for a raised floor must override the values with `--runtime-set` in its own PHPCS CLI invocation, such as its Composer lint script. Only `--runtime-set` has precedence over ruleset-level configuration.

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full backwards-compatibility and release contract.
