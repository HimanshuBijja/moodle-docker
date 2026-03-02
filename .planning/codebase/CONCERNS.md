# Technical Concerns & Debt

## Legacy Code
- **Procedural Core**: Many core files (e.g., `moodlelib.php`, `datalib.php`) remain large and procedural, making them difficult to maintain and test.
- **YUI**: The presence of legacy YUI code in older components creates a split development experience and increases the bundle size.
- **Global Scope**: Heavy reliance on global variables (`$DB`, `$CFG`, etc.) makes isolation of components for unit testing challenging.

## Architecture & Complexity
- **Plugin Fragmentation**: With over 50 plugin types, finding where specific logic resides can be difficult for new developers.
- **Bootstrapping Overhead**: The full core bootstrap is required for almost any request, which can impact performance on high-traffic sites.
- **Hook System Evolution**: Moodle is transitioning from procedural hooks to a modern event/hook system, but both co-exist in many places.

## Performance Considerations
- **Database Query Volume**: Complex pages can execute a large number of database queries. While caching helps, this remains a potential bottleneck.
- **Cache Layer Sensitivity**: Performance is highly dependent on a properly configured cache (e.g., Redis).
- **Task Runner Latency**: Heavy reliance on the cron system for background processing can lead to delays if not properly scheduled.

## Security & Maintenance
- **Third-Party Libraries**: Moodle bundles many libraries (AWS SDK, PHPMailer, etc.) which must be manually updated to address security vulnerabilities.
- **Access Control Complexity**: The granular capability system (`has_capability`) is powerful but can be misconfigured, leading to unintended access.
- **Legacy Database Support**: Supporting older database versions (e.g., specific Oracle or SQL Server versions) can limit the use of modern SQL features.
- **PHP Version Transitions**: Upgrading to new PHP versions often requires extensive testing across the entire plugin ecosystem.
