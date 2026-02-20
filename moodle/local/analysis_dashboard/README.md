# Analysis Dashboard — Moodle Plugin

A comprehensive analytics dashboard plugin for Moodle 4.5+ with **47 widgets**, role-based dashboards, data export, mobile app support, and full CI/CD pipeline.

![Version](https://img.shields.io/badge/version-0.5.0--alpha-blue)
![Moodle](https://img.shields.io/badge/Moodle-4.5%2B-orange)
![PHP](https://img.shields.io/badge/PHP-8.1%20%7C%208.2-purple)
![License](https://img.shields.io/badge/license-GPL--3.0-green)

---

## 📋 Table of Contents

- [Installation](#installation)
- [Accessing the Dashboards](#accessing-the-dashboards)
- [Role Permissions](#role-permissions)
- [Widget Catalog](#widget-catalog)
- [Data Export](#data-export)
- [Mobile App](#mobile-app)
- [Scheduled Tasks](#scheduled-tasks)
- [Development](#development)

---

## Installation

### From ZIP

1. Download or build the plugin ZIP from this repository.
2. In Moodle, go to **Site Administration → Plugins → Install plugins**.
3. Upload the ZIP and follow the on-screen prompts.
4. Complete the database upgrade when prompted.

### Manual (Git)

```bash
# Clone into the Moodle local plugins directory
cd /path/to/moodle/local
git clone <repo-url> analysis_dashboard

# Visit your site as admin to trigger the upgrade
# Or run CLI:
php admin/cli/upgrade.php
```

---

## Accessing the Dashboards

The plugin provides **4 dashboard views** for different roles. After installation, access them via the URLs below (replace `https://your-moodle-site.com` with your actual domain):

### 1. Admin / Site Dashboard

> **URL:** `https://your-moodle-site.com/local/analysis_dashboard/index.php`

The full site-level dashboard with all 47 widgets including server performance, disk usage, and admin-only analytics.

**Who can access:** Site administrators and managers (with `viewsite` capability).

**Navigation:** Appears automatically in the global navigation under **"Analysis Dashboard"**.

---

### 2. Manager Dashboard

> **URL:** `https://your-moodle-site.com/local/analysis_dashboard/managerdashboard.php`

A curated subset of site-level widgets, excluding admin-only widgets like server performance and disk usage. Focused on organizational analytics.

**Who can access:** Managers and administrators (with `viewsite` capability).

**Navigation:** Appears in the global navigation under **"Manager Analytics Dashboard"**.

---

### 3. Course Report

> **URL:** `https://your-moodle-site.com/local/analysis_dashboard/coursereport.php?id={COURSE_ID}`

Course-specific analytics: enrollment stats, grade distribution, activity completion, quiz analytics, at-risk students, and more.

**Who can access:** Teachers and editing teachers of the course (with `viewcourse` capability).

**Navigation:** Appears in the **course settings navigation** as **"Course Report"**.

**Example:** For a course with ID `2`:
```
https://your-moodle-site.com/local/analysis_dashboard/coursereport.php?id=2
```

---

### 4. Student (My Analytics) Dashboard

> **URL:** `https://your-moodle-site.com/local/analysis_dashboard/studentdashboard.php`

Personal analytics for students: learning overview, course progress, grade overview, quiz performance, login history, and upcoming deadlines.

**Who can access:** Any authenticated user (with `viewown` capability).

**Navigation:** Appears in **user profile → My Analytics**.

---

## Role Permissions

| Capability | Description | Default Roles |
|-----------|-------------|---------------|
| `local/analysis_dashboard:viewsite` | Site-level dashboards | Manager |
| `local/analysis_dashboard:viewcourse` | Course-level reports | Teacher, Editing Teacher |
| `local/analysis_dashboard:viewuser` | User-specific reports | Editing Teacher |
| `local/analysis_dashboard:viewown` | Own analytics dashboard | All authenticated users |

> [!TIP]
> Admins have all capabilities by default. To grant managers access to the admin dashboard, assign `viewsite` at the system level via **Site Administration → Users → Permissions → Define roles**.

---

## Widget Catalog

### Site-Level Widgets (Admin + Manager Dashboards)

| Widget | Type | Description |
|--------|------|-------------|
| Total Users | Counter | Active, suspended, deleted user counts |
| Total Courses | Counter | Visible, hidden, total course counts |
| Site Visits | Line chart | Daily/weekly visit trends |
| Authentication Report | Table | Login methods breakdown |
| Enrolled Methods | Bar chart | Enrolment plugin usage |
| Course Categories Overview | Table | Categories with course counts |
| Disk Usage | Pie chart | Storage allocation *(admin only)* |
| Server Performance | Counter | Load, memory, uptime *(admin only)* |
| SecureOTP widgets (15+) | Mixed | OTP analytics, user demographics, security |

### Course-Level Widgets

| Widget | Type | Description |
|--------|------|-------------|
| Enrollment Stats | Counter | Enrolled, active, completed counts |
| Grade Distribution | Bar chart | Grade bands across the course |
| Activity Completion | Heatmap | Student × activity completion matrix |
| Quiz Analytics | Bar/Line | Average scores, attempt distribution |
| At-Risk Students | Table | Students flagged by engagement metrics |
| Course Visits | Line chart | Daily course access trends |
| Recent Activity | Table | Latest student actions |
| Completion Progress | Bar chart | Overall completion rates |
| Students by Rank/Location/Type | Bar/Pie | Demographic breakdowns |

### Student Self-Service Widgets

| Widget | Type | Description |
|--------|------|-------------|
| My Learning Overview | Counter | Enrolled courses, completion rate |
| My Course Progress | Bar chart | Per-course completion percentage |
| My Grade Overview | Table | Grades across all courses |
| My Quiz Performance | Bar chart | Quiz scores and averages |
| My Login History | Line chart | Personal login frequency |
| My Recent Activity | Table | Latest personal actions |
| Upcoming Deadlines | Table | Assignments and quizzes due soon |
| SecureOTP My Profile | Table | Account security status |

---

## Data Export

Table widgets include **CSV** and **Excel** export buttons. Click the export button in any table widget's header to download the data.

- **CSV** — Comma-separated values, opens in any spreadsheet app
- **Excel** — XLSX format, proper column formatting

---

## Mobile App

The plugin includes **Moodle Mobile app support**. Counter-type widgets are displayed natively using Ionic components. To access:

1. Open the Moodle Mobile app
2. Navigate to the **main menu**
3. Tap **"Analysis Dashboard"**

> [!NOTE]
> Chart widgets (line, bar, pie) are not available in the mobile view since Chart.js requires a browser environment. Counter widgets display fully.

---

## Scheduled Tasks

| Task | Schedule | Description |
|------|----------|-------------|
| Aggregate Site Stats | Every hour at :15 | Updates site-level counters |
| Aggregate Course Stats | Every 2 hours at :45 | Updates course-level metrics |
| Calculate Disk Usage | Daily at 3:30 AM | Computes storage allocation |
| Cleanup Stale Cache | Daily at 4:00 AM | Purges expired widget caches |

Manage schedules in **Site Administration → Server → Scheduled tasks**.

---

## Development

### Requirements

- **Moodle** 4.5+ (requires version `2024100100`)
- **PHP** 8.1 or 8.2
- **Database** MariaDB 10.11+ or PostgreSQL 13+

### Running Tests

```bash
# PHPUnit
vendor/bin/phpunit --testsuite local_analysis_dashboard_testsuite

# Behat
php admin/tool/behat/cli/run.php --tags=@local_analysis_dashboard
```

### CI Pipeline

The plugin includes a GitHub Actions CI workflow (`.github/workflows/ci.yml`) that runs:

- PHP Lint, PHPMD, PHPCPD
- Moodle Code Checker (PHPCS)
- PHPUnit tests
- Behat acceptance tests

CI runs automatically on pushes to `main`, `master`, and `dashboards` branches.

### Project Structure

```
local/analysis_dashboard/
├── index.php                    # Admin dashboard entry point
├── managerdashboard.php         # Manager dashboard entry point
├── coursereport.php             # Course report entry point
├── studentdashboard.php         # Student dashboard entry point
├── version.php                  # Plugin version (0.5.0-alpha)
├── lib.php                      # Navigation hooks
├── styles.css                   # Plugin styles + accessibility
├── amd/src/                     # AMD JavaScript modules
│   ├── dashboard.js             # Admin dashboard init
│   ├── course_dashboard.js      # Course dashboard init
│   ├── student_dashboard.js     # Student dashboard init
│   ├── lazy_loader.js           # IntersectionObserver widget loading
│   ├── widget_renderer.js       # Chart.js + counter + table rendering
│   ├── datatable_renderer.js    # Sortable data tables
│   └── export.js                # CSV/Excel export
├── classes/
│   ├── local/
│   │   ├── widget_registry.php  # Widget registration & lookup
│   │   ├── base_widget.php      # Abstract widget base class
│   │   └── widgets/             # 47 widget implementations
│   ├── output/                  # Renderable page classes
│   └── external/                # Web service API
├── db/
│   ├── access.php               # Capability definitions
│   ├── services.php             # External service registration
│   ├── tasks.php                # Scheduled task definitions
│   └── mobile.php               # Mobile app addon config
├── templates/                   # Mustache templates
├── tests/
│   ├── widget_performance_test.php  # PHPUnit tests
│   └── behat/                   # Behat features & step defs
├── mobile/                      # Mobile-specific assets
└── lang/en/                     # English language strings
```

---

## License

GNU GPL v3 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-3.0.html).
