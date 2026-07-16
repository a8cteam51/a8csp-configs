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
- **Config smokes** (`.github/workflows/quality.yml`) — the shared configs are exercised the way a
  consumer's CI will exercise them: `phpcs -e` and a fixture scan prove `phpcs.dist.xml` parses and
  its `<rule ref>`s resolve; a fixture analysis proves `phpstan.dist.neon` runs; `npm run lint:config`
  load-smokes all five Node baselines against the installed toolchain; the block.json reusable runs
  against a schema-valid fixture, and the scripts/styles reusable runs the repo's own `lint:scripts`
  (styles disabled — the repo has no CSS sources to lint). These are workflow-level, not PHPUnit.
- **Mutation** (`.github/workflows/tests-mutation.yml`) — Infection mutates `php/quality-assurance`
  and enforces the MSI floors in `infection.json`, guarding the Unit suite against vacuous assertions.

## Fixtures

`tests/fixtures/` holds the throwaway inputs the tiers above scan: `plugin-stub/` (a minimal,
standards-compliant plugin the PHPCS and PHPStan smokes lint), `node-config/` (the `smoke.mjs`
harness and the probe inputs it feeds through the Node baselines it exercises), and `block-json/`
(a schema-valid block for the block.json
reusable). They exist to give the shared rulesets something real to parse, nothing more.
