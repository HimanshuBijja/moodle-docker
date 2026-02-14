# Testing Patterns

**Analysis Date:** 2025-01-24

## Test Framework

**Runner:**
- PHPUnit and Behat (referenced in CI)
- Config: Standard Moodle `phpunit.xml` and `behat.yml` (expected at site level)

**Assertion Library:**
- PHPUnit standard assertions

**Run Commands:**
```bash
moodle-plugin-ci phpunit              # Run all PHPUnit tests
moodle-plugin-ci behat                # Run Behat features
```

## Test File Organization

**Location:**
- (Not detected) Standard Moodle plugins place tests in a `tests/` directory at the plugin root.

**Naming:**
- (Expected) `*_test.php` for unit tests, `*.feature` for Behat.

## Test Structure

**Suite Organization:**
- (Expected) standard Moodle `advanced_testcase`.

**Patterns:**
- No actual patterns found in the plugin codebase as the `tests/` directory is missing.

## Mocking

**Framework:** PHPUnit Mock Objects.

**Patterns:**
- No custom mocking patterns found.

## Fixtures and Factories

**Test Data:**
- Standard Moodle data generators (`$this->getDataGenerator()`).

**Location:**
- (Not detected)

## Coverage

**Requirements:** None enforced in CI beyond "running" the tests.

## Test Types

**Unit Tests:**
- Intended for widget logic (but missing).

**Integration Tests:**
- Intended for Moodle API interactions (but missing).

**E2E Tests:**
- Behat features (missing).

## Common Patterns

**Async Testing:**
- Not applicable/not detected.

**Error Testing:**
- (Expected) `$this->expectException(...)` in PHPUnit.

---

*Testing analysis: 2025-01-24*
