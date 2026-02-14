# SecureOTP Implementation Guide

## Architecture Overview
The SecureOTP plugin follows a modular architecture with clear separation of concerns:

```
auth.php                    # Main authentication handler
├── classes/
│   ├── auth/              # Authentication logic
│   ├── security/          # Security utilities
│   ├── messaging/         # Communication services
│   ├── storage/           # Data persistence
│   ├── sync/              # User synchronization
│   ├── api/               # API endpoints
│   ├── forms/             # Form definitions
│   ├── tasks/             # Scheduled tasks
│   ├── reports/           # Reporting services
│   ├── event/             # Event handlers
│   └── output/            # Output renderers
```

## Core Components

### Authentication Layer (`classes/auth/`)
- `otp_manager.php`: Handles OTP generation, validation, and storage
- `session_manager.php`: Manages JWT tokens and session state
- `device_fingerprint.php`: Identifies and tracks trusted devices
- `rate_limiter.php`: Implements brute-force protection mechanisms

### Security Layer (`classes/security/`)
- `audit_logger.php`: Maintains immutable logs of authentication events
- `encryption.php`: Provides data encryption and decryption utilities
- `csrf_protection.php`: Manages CSRF token generation and validation
- `input_sanitizer.php`: Validates and sanitizes user inputs

### Messaging Layer (`classes/messaging/`)
- `sms_gateway.php`: Interface for sending SMS messages
- `email_gateway.php`: Interface for sending email notifications
- `message_queue.php`: Asynchronous message queuing system
- `notification_service.php`: Unified notification delivery system

## Data Flow

### Login Process
1. User enters username/password
2. Credentials validated against Moodle user database
3. If OTP enabled for user, redirect to OTP verification page
4. Generate and send OTP to user's registered device
5. User enters received OTP
6. Validate OTP against stored value
7. On success, create authenticated session
8. Log successful authentication event

### OTP Generation
1. Generate cryptographically secure random OTP
2. Store OTP with expiration timestamp
3. Encrypt OTP before storing
4. Send OTP via preferred method (SMS/email)
5. Log OTP generation event

### Session Management
1. On successful authentication, generate JWT token
2. Store session data in Redis/cache
3. Set secure, HTTP-only cookies
4. Implement sliding expiration
5. Handle concurrent session limits

## Database Schema
The plugin extends Moodle's user table with additional fields:
- `secureotp_enabled`: Boolean flag for OTP requirement
- `secureotp_phone`: User's registered phone number
- `secureotp_email`: User's registered email for OTP
- `secureotp_secret`: TOTP secret key (encrypted)
- `secureotp_lastverified`: Timestamp of last successful verification
- `secureotp_recoverycodes`: Encrypted backup recovery codes

## Caching Strategy
- OTP codes stored in Redis with TTL matching validity period
- User session data cached with appropriate expiration
- Rate limiting counters maintained in cache
- Device fingerprints cached for trusted device recognition

## Error Handling
- Comprehensive exception handling throughout
- Graceful degradation when external services unavailable
- Detailed logging for debugging purposes
- User-friendly error messages without revealing sensitive information

## Performance Considerations
- Optimized queries with proper indexing
- Efficient caching to minimize database load
- Asynchronous processing for non-critical operations
- Resource-efficient algorithms for cryptographic operations