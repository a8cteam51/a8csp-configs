# Contributing

## Workflow

Open pull requests against `trunk`. Direct pushes to `trunk` are not permitted.

## Backwards-compatibility contract

Each tagged release must be additive or strictly permissive relative to the preceding tag. Permitted changes include:

- adding an optional `workflow_call` input with a default;
- adding an opt-in PHPCS or PHPStan rule that does not break currently passing code;
- loosening an existing rule; and
- adding a new file.

The following breaking changes are not permitted as routine changes:

- renaming or removing an existing `workflow_call` input;
- removing a ruleset file; and
- tightening a rule that a currently passing consumer relies on without providing an escape hatch.

### Version floors

Changes to the PHP and WordPress version floors are the sanctioned exception to the additive or permissive rule. The floors are enforced by the `testVersion` and `minimum_wp_version` values in `php/quality-assurance/phpcs.dist.xml` and its tests companion, `phpcs.tests.dist.xml`, which move together.

PHPCS fixes an included ruleset's `<config>` values when it loads them. A consuming ruleset's own `<config>` elements therefore cannot override these values, regardless of declaration order. A consumer that is not ready for a raised floor must set its required values with `--runtime-set` in its own PHPCS CLI invocation. Only `--runtime-set` has precedence over ruleset-level configuration.

The consumer sets `testVersion` and `minimum_wp_version` with `--runtime-set` on its own `phpcs` invocation, typically in its `composer.json` lint script.

## Reusable workflow inputs

An existing `workflow_call` input name is frozen once it ships in a tag. Renaming or removing an input requires a major-version discussion before implementation; it must not be introduced in a silent pull request. Adding an optional input with a default is backwards-compatible.

## CI gates

Changes under `.github/workflows/**` must pass actionlint and zizmor through `.github/workflows/reusable-workflow-checks.yml`, as invoked by `.github/workflows/workflow-checks.yml`.

## Consumer validation

Before merging a change under `php/quality-assurance/`, run the changed configuration against a real consumer repository rather than relying only on diff review. Use `a8cteam51/a8csp-plugin-template` as the reference consumer.

## Versioning

This package uses real semantic-version Git tags in the form `vX.Y.Z`. A maintainer creates a tag manually from `trunk` after a meaningful merge. Tags are never automated or force-moved, and a tag is not required for every commit.

- **PATCH**: bug fixes and non-behavioral or documentation fixes.
- **MINOR**: additive or permissive changes, including a new optional input, a new ruleset or file, a new opt-in rule, or a loosened rule.
- **MAJOR**: the sanctioned version-floor-raise path, or a breaking input rename or removal that has received a major-version discussion.
