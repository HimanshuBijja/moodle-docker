# External Integrations

**Analysis Date:** 2026-03-02

## APIs & External Services
**Moodle Core APIs:**
- `mod_feedback` - Data source for feedback analysis widgets.
- `mod_quiz` - Data source for quiz analytics.
- `core_user` - User data and profile information.
- `core_course` - Course structure and enrollment data.

## Data Storage
**Databases:**
- Moodle Database - Standard tables + `local_analysis_dashboard` specific tables (if any, primarily uses core logs and stats).

**Caching:**
- Moodle Universal Cache (MUC) - Uses application-level caches: `sitestats`, `coursestats`, `diskusage`, `userstats`.

## Authentication & Identity
**Auth Provider:**
- `auth_secureotp` - Optional integration. The plugin detects if `auth_secureotp_security` table exists and provides specialized security widgets.

## Monitoring & Observability
**Performance:**
- Server performance monitoring via `/proc/meminfo` reading (internal system call).

## CI/CD & Deployment
**Mobile Support:**
- Moodle Mobile App - Integrated via `db/mobile.php` and `classes/output/mobile.php`, providing simplified counter-type widgets for the mobile interface.

## Webhooks & Callbacks
**Hooks:**
- `db/hooks.php` - Uses Moodle hooks for performance tracking and data aggregation.