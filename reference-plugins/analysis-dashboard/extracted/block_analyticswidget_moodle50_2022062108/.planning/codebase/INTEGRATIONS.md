# External Integrations

**Analysis Date:** 2025-01-24

## APIs & External Services

**Moodle Mobile:**
- Moodle App integration via `db/mobile.php` and `classes/output/mobile.php`
- Uses `CoreBlockDelegate` for mobile app display

## Data Storage

**Databases:**
- Standard Moodle Database (PostgreSQL/MariaDB supported via CI)
- Key tables accessed: `course`, `role_assignments`, `context`

**File Storage:**
- Local filesystem for icons and templates

**Caching:**
- Moodle Cache API (MUC)
- Cache definition: `awstat` in `db/caches.php`
- Mode: `MODE_APPLICATION`
- TTL: 3600 seconds (1 hour)

## Authentication & Identity

**Auth Provider:**
- Moodle Core Authentication
- Permission-based access via `db/access.php` (e.g., `block/analyticswidget:myaddinstance`)

## Monitoring & Observability

**Error Tracking:**
- Standard Moodle error handling and logging

**Logs:**
- Moodle event log (implicitly handled by Moodle framework)

## CI/CD & Deployment

**Hosting:**
- Self-hosted Moodle environment

**CI Pipeline:**
- GitHub Actions via `.github/workflows/ci.yml`
- Runs PHP Lint, PHPMD, CodeChecker, Grunt, PHPUnit, and Behat

## Environment Configuration

**Required env vars:**
- Standard Moodle `config.php` variables

**Secrets location:**
- Moodle `config.php` (not stored in plugin)

## Webhooks & Callbacks

**Incoming:**
- `mobile_view` method in `classes/output/mobile.php` serves as a callback for the Moodle Mobile App

---

*Integration audit: 2025-01-24*
