# Scripts contract

This document names the composer and npm script identifiers the reusable workflows in this
repository treat as their interface. A consuming project's `composer.json` and `package.json`
scripts must use these names for the corresponding reusable workflow to find and run them.

## Fixed names

These names are hardcoded in the reusable workflow and are not configurable via a `workflow_call`
input.

| Script | Ecosystem | Workflow | Condition |
| --- | --- | --- | --- |
| `lint:scripts` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-scripts` input is `true` (default). |
| `lint:styles` | npm | `reusable-scripts-styles-lint.yml` | Runs when the `lint-styles` input is `true` (default). |

## Default names (input-overridable)

These names are the default value of a `workflow_call` input; a caller may override them, but
matching the default avoids an unnecessary `with:` entry.

| Script | Ecosystem | Workflow | Input |
| --- | --- | --- | --- |
| `test` | composer | `reusable-phpunit.yml` | `composer-script` |
| `build` | npm | `reusable-playwright-e2e.yml` | `build-script` |
| `test:e2e` | npm | `reusable-playwright-e2e.yml` | `playwright-script` |

## Convention (consumer-declared, not enforced by the workflow)

`reusable-php-lint.yml` takes a required `scripts` array of composer script names and runs each as
its own matrix job; the workflow itself names none of them. The established convention across
consumers is a `lint:php` composer script that aggregates `lint:php:phpcs` and `lint:php:phpstan`,
passed as `'["lint:php:phpcs", "lint:php:phpstan"]'`.

## Out of scope

This repository ships no reusable release or changelog workflow, so no changelog script name is
part of this contract.
