# Architecture

**Analysis Date:** 2024-05-24

## Pattern Overview

**Overall:** Modular Plugin-based Architecture (Moodle: Modular Object-Oriented Dynamic Learning Environment)

**Key Characteristics:**
- **Plugin-driven:** Almost every feature is a plugin (Activities, Blocks, Auth, Enrol, etc.).
- **Global Registry:** Uses global variables (`$CFG`, `$DB`, `$PAGE`, `$USER`, `$OUTPUT`) for shared state and services.
- **Event-Driven:** Uses an event and hook system for cross-module communication and extension.

## Layers

**Core Layer:**
- Purpose: Provides the base infrastructure, API, and orchestration.
- Location: `moodle/lib/`, `moodle/admin/`
- Contains: DML/DDL (database), File storage API, Output API, Autoloader.

**Plugin Layer:**
- Purpose: Implements specific learning or system features.
- Location: `moodle/mod/`, `moodle/auth/`, `moodle/theme/`, etc.
- Contains: Activity logic, authentication methods, user interfaces.

**Presentation Layer:**
- Purpose: Decouples logic from display.
- Location: `moodle/theme/`, `moodle/*/templates/`, `moodle/*/renderer.php`
- Contains: Mustache templates and PHP Renderer classes.

## Data Flow

**Standard Web Request:**
1. Request enters via an entry point (e.g., `moodle/index.php` or `moodle/course/view.php`).
2. `config.php` is loaded, triggering `moodle/lib/setup.php`.
3. Globals (`$DB`, `$USER`, etc.) are initialized.
4. Plugin logic is executed (often via autoloaded classes in `classes/`).
5. Output is prepared using `$PAGE` and rendered via `$OUTPUT`.

## Key Abstractions

**Database Access ($DB):**
- Purpose: Abstraction layer for database operations (DML).
- Location: `moodle/lib/dml/`

**Output Renderer ($OUTPUT):**
- Purpose: Handles HTML generation and template rendering.
- Examples: `moodle/lib/outputrenderers.php`
