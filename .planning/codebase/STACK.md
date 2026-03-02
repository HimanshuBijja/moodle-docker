# Tech Stack

## Backend
- **Language**: PHP 8.1+ (Minimum version 8.1.0 as per `composer.json`)
- **Framework**: Custom Moodle framework (mature, modular, plugin-based)
- **Key Bundled Components**: Symfony components (Mime, Polyfill, etc.), AWS SDK for PHP, Guzzle, PHPMailer, Mustache.php.
- **Node.js**: Required version 22.11.x (for development and build tools).

## Database
- **Supported Engines**: PostgreSQL, MySQL, MariaDB, Oracle, SQL Server.
- **Abstraction Layer**: Custom Database Manipulation Layer (DML) and Database Definition Layer (DDL).

## Frontend
- **Module System**: AMD modules (RequireJS).
- **Templating**: Mustache templates.
- **Styling**: SASS (compiled to CSS).
- **Build Tools**: Grunt, Babel (for ES6+ transpilation), Shifter (for YUI).
- **Legacy Systems**: YUI (Yahoo! User Interface Library) is still present in some older components.

## Infrastructure & Environment
- **Web Server**: Apache or Nginx (typical for Moodle).
- **Cache**: Supports APCu, Redis, Memcached, and file-based caching.
- **Task Runner**: Cron system for scheduled tasks and background processing.
