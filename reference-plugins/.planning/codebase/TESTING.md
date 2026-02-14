# Testing Patterns

**Analysis Date:** 2026-02-14

## Test Framework

**Runner:**
- **Behat:** Used for E2E and UI testing. Standard Moodle tool.
- **PHPUnit:** Supported in CI configurations but not actively implemented in most reference plugins.
- **Moodle-Plugin-CI:** Orchestrator used in GitHub Actions and Travis CI to run linting and tests.

**Assertion Library:**
- PHPUnit/Behat native assertions.

**Run Commands:**
```bash
# General Moodle command (from plugin root in a Moodle env)
vendor/bin/phpunit --group [plugin_component_name]
vendor/bin/behat --tags @[plugin_component_name]

# Using moodle-plugin-ci (as seen in CI workflows)
moodle-plugin-ci phpunit
moodle-plugin-ci behat
```

## Test File Organization

**Location:**
- Dedicated `tests/` directory at the plugin root.
- Behat features: `tests/behat/*.feature`.
- PHPUnit tests: `tests/*_test.php` (Moodle convention).

**Naming:**
- Features: `[context].feature` (e.g., `reports_management.feature`)
- PHPUnit: `[class]_test.php`

**Structure:**
```
[plugin-root]/
└── tests/
    └── behat/
        └── [name].feature
```

## Test Structure

**Behat Suite Organization:**
```gherkin
@report @report_lmsace_reports @report_lmsace_reports_management
Feature: Setup different configurations to customize the reports
  In order to use the reports
  As admin
  I need to be able to configure the lmsace reports plugin

  @javascript
  Scenario Outline: Enable or disable the reports to show or hide from users
    Given I log in as "admin"
    And I navigate to "Plugins > Reports > LMSACE reports" in site administration
    ...
```
*(Source: `report_lmsace_reports/tests/behat/reports_management.feature`)*

**PHPUnit Patterns:**
Not observed in the reference plugins. Most plugins rely on CI for linting and validation rather than unit testing.

## Mocking

**Framework:** Not observed.

**Patterns:**
No explicit mocking patterns detected in the reference plugins.

## Fixtures and Factories

**Test Data:**
Standard Moodle Behat generators are used:
```gherkin
And the following "courses" exist:
  | fullname | shortname |
  | Course 1 | C1        |
```

**Location:**
Moodle core generators are leveraged rather than local fixtures.

## Coverage

**Requirements:** None enforced in the observed reference plugins.

## Test Types

**Unit Tests:**
- Generally missing from reference plugins.
- `local_kopere_dashboard` has a file `classes/report/report_benchmark_test.php`, but it is a diagnostic class extending functionality, not an automated unit test.

**Integration Tests:**
- Generally missing.

**E2E Tests:**
- **Behat:** Observed in `report_lmsace_reports`. Focuses on configuration settings and UI visibility of report widgets.

## Common Patterns

**UI Validation:**
Behat tests focus on verifying the presence or absence of specific CSS elements after configuration changes.

**CI Integration:**
Almost all plugins include GitHub Actions workflows (`.github/workflows/ci.yml`) using `moodlehq/moodle-plugin-ci`. Even if they lack automated tests, they run:
- `phplint`
- `phpcs` (Code Checker)
- `phpmd` (Mess Detector)
- `validate` (Moodle plugin validation)
- `mustache` (Mustache lint)

---

*Testing analysis: 2026-02-14*
