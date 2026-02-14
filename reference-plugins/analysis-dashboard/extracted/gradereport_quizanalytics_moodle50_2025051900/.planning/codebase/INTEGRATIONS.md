# External Integrations

**Analysis Date:** 2025-02-15

## APIs & External Services

**Moodle Web Services:**
- `moodle_quizanalytics_analytic` - Custom external function defined in `externallib.php` to fetch analytics data.
  - SDK/Client: `core/ajax` (Moodle AMD module)
  - Auth: Handled by Moodle session/Web Service tokens.

## Data Storage

**Databases:**
- Moodle Database (MySQL/PostgreSQL/MariaDB/MSSQL)
  - Connection: Handled by Moodle `$DB` global.
  - Client: Moodle DB API.
  - Tables accessed: `{quiz}`, `{quiz_attempts}`, `{question}`, `{question_attempts}`, `{question_attempt_steps}`, `{question_categories}`, `{question_references}`, `{question_bank_entries}`, `{question_versions}`.

**File Storage:**
- Moodle File API - Used for plugin icons (e.g., `pix/downloadicon.png`).

**Caching:**
- Moodle Universal Cache (MUC) - Potentially used by core APIs, but no explicit custom cache definitions found.

## Authentication & Identity

**Auth Provider:**
- Moodle Core Authentication
  - Implementation: `require_login($course)` and `require_capability()` checks in `index.php`.

## Monitoring & Observability

**Error Tracking:**
- Moodle Error Logs (Web server logs and Moodle debugging output).

**Logs:**
- Standard Moodle logging via `\core\log\manager`.

## CI/CD & Deployment

**Hosting:**
- Any Moodle-compatible hosting (On-premise or Cloud).

**CI Pipeline:**
- Not detected.

## Environment Configuration

**Required env vars:**
- Standard Moodle `config.php` variables (`$CFG->wwwroot`, `$CFG->dataroot`, `$CFG->dbname`, etc.).

**Secrets location:**
- Moodle `config.php`.

## Webhooks & Callbacks

**Incoming:**
- AJAX calls to `lib/ajax/service.php` targeting the `moodle_quizanalytics_analytic` function.

**Outgoing:**
- None detected.

---

*Integration audit: 2025-02-15*
