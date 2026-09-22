# Contributing

## Workflow

Open pull requests against `trunk`. Direct pushes to `trunk` are not permitted.

## Backwards-compatibility contract

The contract covers everything a consumer references: the reusable workflow files and their `workflow_call` inputs, the PHP ruleset and config files, the `package.json` exports, the `prettier` alias to `wp-prettier` that consumers resolve through this package, and the script names under Fixed names and Default names in [the scripts contract](docs/scripts-contract.md).

Each tagged release must be additive or strictly permissive relative to the preceding tag. Permitted changes include:

- adding an optional `workflow_call` input with a default;
- adding an opt-in PHPCS or PHPStan rule that does not break currently passing code;
- loosening an existing rule; and
- adding a new file.

The following breaking changes are not permitted as routine changes:

- renaming or removing an existing `workflow_call` input;
- renaming or removing a reusable workflow file, a ruleset or config file, or a `package.json` export;
- repointing or removing the `prettier` alias;
- renaming a Fixed or Default script name; and
- tightening a rule that a currently passing consumer relies on without providing an escape hatch.

### Version floors

Changes to the PHP and WordPress version floors are one sanctioned exception to the additive or permissive rule. The floors are enforced by the `testVersion` and `minimum_wp_version` values in `php/quality-assurance/phpcs.base.dist.xml`, which both PHPCS rulesets include.

PHPCS fixes an included ruleset's `<config>` values when it loads them. A consuming ruleset's own `<config>` elements therefore cannot override these values, regardless of declaration order. A consumer that is not ready for a raised floor must set its required values with `--runtime-set` in its own PHPCS CLI invocation, typically in its `composer.json` lint script. Only `--runtime-set` has precedence over ruleset-level configuration.

### Tool runtimes

Moving a tool runtime forward is the other sanctioned exception. The reusable workflows run their tools on PHP and Node versions this package fixes, including the defaults of their version inputs. A consumer's floors, not these runtimes, decide what its code is checked against, so a newer runtime keeps checking code written for older floors. Such a move is a MINOR change once both reference consumers under [Consumer validation](#consumer-validation) pass their gates on it, and a MAJOR change when it requires consumers to change.

## npm peer dependencies

The `node/` configs import the packages listed under `peerDependencies`, and a consumer's `@wordpress/scripts` brings its own copies of them. Declare each peer as a minimum (`>=`), never a caret range: a caret range caps the major, so a consumer on the next `@wordpress/scripts` major installs a second, older copy just for this package, and the shared configs silently load the older rules. Raise a floor only to a version this repository's own `devDependencies` install and test.

## CI gates

Changes under `.github/workflows/**` and to `.github/dependabot.yml` must pass actionlint and zizmor through `.github/workflows/reusable-workflow-checks.yml`, as invoked by `.github/workflows/workflow-checks.yml`; zizmor audits the Dependabot config as well as the workflows.

## Consumer validation

Before merging a change under `php/quality-assurance/`, run the changed configuration against a real consumer repository rather than relying only on diff review. Use the plugin and site templates, `a8cteam51/a8csp-plugin-template` and `a8cteam51/a8csp-project-template`, as reference consumers.

## Versioning

This package uses real semantic-version Git tags in the form `vX.Y.Z`. A maintainer creates a tag manually from `trunk` after a meaningful merge. Tags are never automated or force-moved, and a tag is not required for every commit.

- **PATCH**: bug fixes and non-behavioral or documentation fixes.
- **MINOR**: additive or permissive changes, including a new optional input, a new ruleset or file, a new opt-in rule, or a loosened rule; and a tool-runtime move that both reference consumers pass.
- **MAJOR**: the sanctioned version-floor-raise path, a tool-runtime move that requires consumers to change, or a breaking input rename or removal that went through a major-version discussion before implementation.

### Supported versions

Only the latest major version is supported: fixes land on `trunk` and ship in its next tag, with no backports to an earlier major. Majors are rare and batch their breaking changes, at most one a year, and each documents its migration steps. Tags never move, so a consumer pinned to an earlier tag keeps that tag's behavior until it next changes, and then moves to the latest major.
