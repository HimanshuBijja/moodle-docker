# Testing Patterns

**Analysis Date:** 2026-02-15

## Test Framework

**Runner:**
- PHPUnit (via Moodle's built-in support)
- Base Class: `\advanced_testcase`

**Assertion Library:**
- PHPUnit built-in assertions (`assertTrue`, `assertEquals`, `assertMatchesRegularExpression`, etc.)

**Run Commands:**
```bash
# Standard Moodle PHPUnit run (must be configured at Moodle root)
php admin/tool/phpunit/cli/testrunner.php --group auth_secureotp
```

## Test File Organization

**Location:**
- PHPUnit: `tests/` directory at plugin root.
- Behat: `tests/behat/` (Currently empty).

**Naming:**
- Files: `*_test.php` (e.g., `auth_test.php`, `otp_test.php`).
- Classes: Match filename (e.g., `class auth_test`).

**Structure:**
```
secureotp/
├── tests/
│   ├── auth_test.php
│   ├── import_test.php
│   ├── otp_test.php
│   ├── fixtures/
│   │   ├── users/
│   │   └── otp/
│   ├── phpunit/ (Empty)
│   └── behat/ (Empty)
└── test_users_100.csv (Fixtures)
```

## Test Structure

**Suite Organization:**
```php
namespace auth_secureotp;

defined('MOODLE_INTERNAL') || die();

/**
 * @group auth_secureotp
 */
class auth_test extends \advanced_testcase {
    public function test_example() {
        $this->resetAfterTest(true);
        // ... implementation
    }
}
```

**Patterns:**
- **Setup:** Uses `$this->resetAfterTest(true)` to ensure database isolation.
- **Data Generation:** Uses `$this->getDataGenerator()->create_user()` for Moodle entities.
- **Teardown:** Handled automatically by `resetAfterTest`.
- **Assertion:** Direct assertions on method return values.

## Mocking

**Framework:** PHPUnit built-in Mock Objects.

**Patterns:**
- No explicit complex mocking observed in existing tests; primarily uses integration-style tests with real (but reset) database.

**What to Mock:**
- Recommended: External gateways (SMS, Email) to avoid side effects.
- Guidelines: Mock objects that interact with 3rd party APIs (e.g., Twilio).

## Fixtures and Factories

**Test Data:**
- CSV file `test_users_100.csv` used for testing user import functionality.
- Manual record insertion via `$DB->insert_record` for secondary tables.

**Location:**
- `tests/fixtures/` and root of the `secureotp/` directory.

## Coverage

**Requirements:** None explicitly stated in code.

**View Coverage:**
- Not configured.

## Test Types

**Unit Tests:**
- `otp_test.php`: Tests generation and validation logic for OTPs.
- `import_test.php`: Tests CSV validation and import logic.

**Integration Tests:**
- `auth_test.php`: Tests the full authentication flow including interaction with `$DB`, `$SESSION`, and multiple plugin classes (`rate_limiter`, `input_sanitizer`).

**E2E Tests:**
- Not used (Behat directory is empty).

## Common Patterns

**Async Testing:**
- Not detected (Moodle PHPUnit is primarily synchronous).

**Error Testing:**
- Checks return arrays for `success => false` and specific `error_code`.
```php
$result = $auth->initiate_otp_login('NONEXISTENT');
$this->assertFalse($result['success']);
$this->assertEquals('USER_NOT_FOUND', $result['error_code']);
```

---

*Testing analysis: 2026-02-15*
