# Testing Patterns

**Analysis Date:** 2025-03-02

## Test Framework

**Runner:**
- **PHPUnit** (Moodle core runner) for unit and integration tests.
- **Behat** (Gherkin-based) for end-to-end (E2E) functional tests.
- Config: Moodle uses a global `phpunit.xml` and `behat.yml` at the root (not local to `user/`).

**Assertion Library:**
- PHPUnit's built-in assertions (`assertEquals`, `assertNull`, `assertIsArray`, etc.).

**Run Commands:**
```bash
# General Moodle test commands (run from root)
vendor/bin/phpunit user/tests/userlib_test.php    # Run specific test file
vendor/bin/behat --tags=@core_user                # Run Behat tests for this component
```

## Test File Organization

**Location:**
- PHPUnit tests: `user/tests/`.
- Behat tests: `user/tests/behat/`.

**Naming:**
- PHPUnit: `[feature]_test.php`. Example: `user/tests/userlib_test.php`
- Behat: `[feature].feature`. Example: `user/tests/behat/addnewuser.feature`

**Structure:**
```
user/tests/
├── behat/                # E2E features and steps
│   ├── behat_user.php    # Custom steps for users
│   └── *.feature         # Feature definitions
├── external/             # Tests for web services
├── fixtures/             # Test files/data
└── *_test.php            # Unit/Integration tests
```

## Test Structure

**Suite Organization:**
```php
namespace core_user;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/user/lib.php');

final class userlib_test extends \advanced_testcase {
    public function test_example(): void {
        $this->resetAfterTest(); // Reset DB state
        // Setup, Execute, Assert
    }
}
```

**Patterns:**
- **Setup pattern:** `resetAfterTest()` is mandatory for tests that modify the database. `getDataGenerator()` creates realistic Moodle objects (users, courses, etc.).
- **Teardown pattern:** Moodle handles DB rollback automatically after each test if `resetAfterTest()` is used.
- **Assertion pattern:** AAA (Arrange-Act-Assert) pattern is used within test methods.

## Mocking

**Framework:** Moodle uses its own data generator and `setUser()`/`setAdmin()` for state mocking.

**Patterns:**
```php
// Mocking the current user
$this->setUser($user1);

// Using the data generator for entities
$user = $this->getDataGenerator()->create_user();
```

**What to Mock:**
- Current user context (`setUser`).
- Global `$DB` is not usually mocked; instead, tests run against a real test database.
- External system calls should be mocked.

**What NOT to Mock:**
- Database calls (prefer using the test DB with `resetAfterTest`).
- Internal Moodle APIs that have side effects (use the real API and verify the side effect).

## Fixtures and Factories

**Test Data:**
```php
// Creating a user with custom fields
$user = $this->getDataGenerator()->create_user(['firstname' => 'John', 'email' => 'john@example.com']);
```

**Location:**
- Fixtures are located in `user/tests/fixtures/`.

## Coverage

**Requirements:** High coverage is expected for core components. Moodle tracks this via its CI system.

**View Coverage:**
```bash
# Typically from root
vendor/bin/phpunit --coverage-html coverage/ user/tests/
```

## Test Types

**Unit Tests:**
- Scope: Individual functions in `lib.php` or classes.
- Approach: Fast, isolated, uses `advanced_testcase`.

**Integration Tests:**
- Scope: External APIs in `externallib.php`, interactions with the database.
- Approach: Uses `advanced_testcase`, involves DB state via `getDataGenerator`.

**E2E Tests:**
- Framework: Behat.
- Scope: User journeys through the UI (e.g., adding a new user, editing profile).
- Approach: Uses Gherkin features and Selenium/Mink.

## Common Patterns

**Async Testing:**
- JS (AMD): Use the `Pending` module to ensure tests wait for async operations to complete.
- Behat: Uses `I wait until ...` or implicit waits in Mink.

**Error Testing:**
- PHPUnit: `expectException(moodle_exception::class);`.
- Behat: `Then I should see "Error message"`.

---

*Testing analysis: 2025-03-02*
