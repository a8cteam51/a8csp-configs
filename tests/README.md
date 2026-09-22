# Tests

The test tiers prove that the shared configs parse and behave; the package ships configuration, not
a WordPress runtime.

## Tiers

- **Unit** (`tests/Unit/`) — plain PHPUnit, no WordPress. `PhpstanBootstrapTest` probes the
  discovery logic in `php/quality-assurance/phpstan.dist.neon.php`: it `chdir`s into a throwaway
  project directory, `require`s the file, and asserts on the returned config array. The file has no
  coverable named symbol, so its behavior is proven by assertion rather than attributed line
  coverage.
- **wp-env consumer smoke** (`.github/workflows/quality.yml`, `tests/fixtures/wp-env-consumer/`) —
  the one tier that starts WordPress, to cover this repository's own PHPUnit workflow. The workflow
  runs once with `needs-wp-env: false` and once against the fixture consumer, which declares
  `@wordpress/env` like a real project so the workflow starts the binary its lockfile pins. The
  fixture's `smoke.sh` starts wp-env again, as a consumer's own test script may, then asserts from
  inside the container that the caller's `wp-version` reached it and that its mapped tree is served.
- **Config smokes** (`.github/workflows/quality.yml`) — workflow-level, not PHPUnit: `phpcs -e` and
  fixture scans prove both PHPCS profiles parse and resolve; a scan at the shared floors and one
  with both lowered through `--runtime-set` prove a consumer can keep its own PHP and WordPress
  floors; a scan of a mixed file tree proves the exclude-patterns skip only generated and
  third-party files; a fixture analysis proves `phpstan.dist.neon` runs; `npm run lint:config`
  load-smokes the Node baselines against the installed toolchain, beside the repository's own
  `lint:scripts`; the block.json workflow runs against a schema-valid fixture.

## Not covered here

This repository's own CI runs five of its reusable workflows: block-json-check, codeql, phpunit,
supply-chain-audit and workflow-checks. The other five (php-lint, php-syntax-check,
playwright-e2e, release and scripts-styles-lint) get only static analysis here (actionlint, zizmor
and CodeQL), so a change to one of them first runs for real in a consumer's CI.

## Fixtures

`tests/fixtures/` holds the inputs the tiers above scan: `plugin-stub/` (a minimal,
standards-compliant plugin for the PHPCS and PHPStan smokes), `php-compat/` (deprecated constructs
the PHPCompatibility check must flag), `floor-opt-out/` (one construct per floor whose report
changes when a consumer lowers that floor), `phpcs-exclusions/` (hand-written files the shared
exclude-patterns must scan, beside generated and third-party ones they must skip), `node-config/`
(the `smoke.mjs` harness and its probe inputs), and `block-json/` (a schema-valid block).

`wp-env-consumer/` is a working project rather than an input, with its own `composer.json`,
`package.json` and lockfile, because the workflow under test installs and runs a consumer's
dependencies. Dependabot tracks its `@wordpress/env`, so the smoke keeps exercising the wp-env
consumers run.
