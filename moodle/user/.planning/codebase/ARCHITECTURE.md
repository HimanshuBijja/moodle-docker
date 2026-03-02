# Architecture

**Analysis Date:** 2025-02-12

## Pattern Overview

**Overall:** Hybrid Architecture (Transitioning from Legacy Procedural to Modern Object-Oriented)

**Key Characteristics:**
- **Legacy Core:** Centralized procedural functions in `user/lib.php` for CRUD operations and core user logic.
- **Modern Layer:** Increasing use of Namespaced OO classes in `user/classes/` for specific responsibilities like field management and data exporting.
- **Extensible Profile System:** Plugin-based architecture for custom profile fields located in `user/profile/`.
- **Component-Based UI:** Shift from server-side PHP rendering to Mustache templates (`user/templates/`) and AMD/ESM JavaScript modules (`user/amd/`).

## Layers

**Core Logic Layer (Legacy):**
- Purpose: Provides foundational user management functions.
- Location: `user/lib.php`
- Contains: Global functions like `user_create_user`, `user_update_user`, `user_delete_user`.
- Depends on: Global `$DB`, `$CFG`.
- Used by: Almost all entry points and external APIs.

**Core Logic Layer (Modern):**
- Purpose: Encapsulates specific user-related logic in reusable classes.
- Location: `user/classes/`
- Contains: `core_user\fields` for field handling, `core_user\output\*` for UI data preparation.
- Depends on: Moodle core APIs.
- Used by: External APIs, UI controllers.

**External API Layer:**
- Purpose: Exposes user functionality to external systems and the mobile app.
- Location: `user/externallib.php` (Legacy) and `user/classes/external/` (Modern).
- Contains: `core_user_external` class and various Exporters.
- Depends on: Core logic layer.

**UI Layer:**
- Purpose: Handles user interactions and data presentation.
- Location: Root PHP files (e.g., `user/view.php`), `user/templates/`, and `user/amd/`.
- Contains: PHP controllers, Mustache templates, and JavaScript modules.
- Depends on: Core logic and External API layers.

## Data Flow

**User Profile Viewing:**
1. Request hits `user/profile.php` or `user/view.php`.
2. Logic retrieves user data using `user_get_user_by_id` or similar from `user/lib.php`.
3. Profile fields are processed via `user/profile/lib.php` and specific field classes in `user/profile/field/*/`.
4. Renderer (procedural or OO via `user/renderer.php`) prepares the output.
5. Modern components may use Exporters in `user/classes/external/` to pass data to Mustache templates.

**User Filtering/Searching:**
1. `user/index.php` handles the user listing.
2. Filter logic in `user/filters/` processes search criteria.
3. Results are displayed using table classes in `user/classes/table/`.

## Key Abstractions

**core_user\fields:**
- Purpose: Centralized management of standard and custom user fields.
- Examples: `user/classes/fields.php`
- Pattern: Factory and Fluent Interface.

**profile_field_base:**
- Purpose: Base class for all custom user profile fields.
- Examples: `user/profile/lib.php`, `user/profile/field/text/field.class.php`
- Pattern: Template Method / Strategy.

**Exporters:**
- Purpose: Standardized way to prepare data for Mustache templates.
- Examples: `user/classes/external/user_summary_exporter.php`
- Pattern: Data Transfer Object (DTO) / Presenter.

## Entry Points

**Profile View:**
- Location: `user/view.php`
- Triggers: User clicks on a profile link.
- Responsibilities: Displays user details, course enrolments, and various logs/reports.

**User Management:**
- Location: `user/edit.php` / `user/editadvanced.php`
- Triggers: Admin or user editing a profile.
- Responsibilities: Handles form display and submission for user data updates.

**User List:**
- Location: `user/index.php`
- Triggers: Navigation to site participants or user management pages.
- Responsibilities: Lists users with filtering and bulk action support.

## Error Handling

**Strategy:** Exception-based error handling.

**Patterns:**
- Throwing `moodle_exception` for business logic failures in `user/lib.php`.
- External API functions returning `external_warnings` or throwing exceptions that are caught by the web service layer.

## Cross-Cutting Concerns

**Logging:** Uses the Moodle Events API (found in `classes/event/`, though not explored in detail, it's standard for core components).
**Validation:** Handled primarily via `moodleform` in `user/edit_form.php` and similar files.
**Authentication:** Integrated with Moodle's core authentication plugins; user creation logic in `lib.php` respects auth plugin requirements.

---

*Architecture analysis: 2025-02-12*
