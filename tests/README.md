# Tests

`a8csp-configs` ships configuration, not a runtime WordPress artifact, so its test tiers prove that
the shared configs parse and behave — not that a plugin boots. There is deliberately no WordPress
integration or browser tier here; those live in the consuming templates, which exercise the configs
in a real runtime.

## Tiers

- **Unit** (`tests/Unit/`) — plain PHPUnit, no WordPress. Today this is `PhpstanBootstrapTest`, a
  filesystem-fixture probe of the procedural discovery logic in
  `php/quality-assurance/phpstan.dist.neon.php`: it `chdir`s into a throwaway project directory,
  `require`s the file, and asserts on the returned config array. The target is a returns-array file
  with no coverable named symbol, so its behaviour is proven by assertion rather than attributed
  line coverage.
- **wp-env consumer smoke** (`.github/workflows/quality.yml`, `tests/fixtures/wp-env-consumer/`) —
  the one tier that starts WordPress, and it does so to cover this repository's own workflow rather
  than a plugin. The PHPUnit reusable is dogfooded twice: once with `needs-wp-env: false`, which
  leaves its wp-env branch untested, and once against the fixture consumer, which declares
  `@wordpress/env` like a real project so the workflow starts the binary its lockfile pins. The
  fixture's `smoke.sh` asserts from inside the container that the caller's `wp-version` reached it
  and that its mapped tree is served — the two things those steps are responsible for.
- **Config smokes** (`.github/workflows/quality.yml`) — the shared configs are exercised the way a
  consumer's CI will exercise them: `phpcs -e` and a fixture scan prove `phpcs.dist.xml` parses and
  its `<rule ref>`s resolve; a fixture analysis proves `phpstan.dist.neon` runs; `npm run lint:config`
  load-smokes all five Node baselines against the installed toolchain; the block.json reusable runs
  against a schema-valid fixture, and the scripts/styles reusable runs the repo's own `lint:scripts`
  (styles disabled — the repo has no CSS sources to lint). These are workflow-level, not PHPUnit.

## Fixtures

`tests/fixtures/` holds the throwaway inputs the tiers above scan: `plugin-stub/` (a minimal,
standards-compliant plugin the PHPCS and PHPStan smokes lint), `node-config/` (the `smoke.mjs`
harness and the probe inputs it feeds through the Node baselines it exercises), and `block-json/`
(a schema-valid block for the block.json
reusable). They exist to give the shared rulesets something real to parse, nothing more.

`wp-env-consumer/` is the exception: it is a working project rather than an input, with its own
`composer.json`, `package.json` and lockfile, because the workflow under test installs and runs a
consumer's dependencies. Its `@wordpress/env` is dependabot-tracked for that reason — a fixture
frozen at an old wp-env would still exercise the workflow while quietly ceasing to exercise the
version anybody runs.
