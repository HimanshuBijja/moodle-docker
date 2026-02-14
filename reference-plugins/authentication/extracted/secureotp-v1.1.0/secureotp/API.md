# SecureOTP API Documentation

## Overview
The SecureOTP plugin provides a comprehensive REST API for programmatic access to authentication functions, user management, and reporting capabilities.

## Base URL
```
https://your-moodle-site.com/auth/secureotp/classes/api/rest_controller.php
```

## Authentication
All API requests require authentication using one of the following methods:
- OAuth 2.0 tokens (recommended)
- API key authentication
- JWT tokens for session-based access

## Rate Limiting
- Standard requests: 100 requests per hour per IP
- Authentication requests: 10 requests per minute per IP
- Bulk operations: 10 requests per hour per IP

## API Endpoints

### Authentication Endpoints

#### POST /api/authenticate
Initiate the authentication process with username and password.

**Request Body:**
```json
{
  "username": "user@example.com",
  "password": "user_password"
}
```

**Response:**
```json
{
  "success": true,
  "requires_otp": true,
  "session_token": "jwt_token",
  "otp_method": "sms|email|totp",
  "expires_in": 300
}
```

#### POST /api/verify_otp
Verify the OTP code provided by the user.

**Request Body:**
```json
{
  "session_token": "jwt_token",
  "otp_code": "123456",
  "device_fingerprint": "unique_device_id"
}
```

**Response:**
```json
{
  "success": true,
  "authenticated": true,
  "redirect_url": "/dashboard",
  "session_id": "session_identifier"
}
```

#### POST /api/resend_otp
Resend the OTP code if the user hasn't received it.

**Request Body:**
```json
{
  "session_token": "jwt_token"
}
```

**Response:**
```json
{
  "success": true,
  "otp_resent": true,
  "expires_in": 300
}
```

### User Management Endpoints

#### GET /api/user/{id}
Retrieve user authentication settings.

**Parameters:**
- `id`: User ID

**Response:**
```json
{
  "id": 123,
  "username": "johndoe",
  "otp_enabled": true,
  "otp_methods": ["sms", "email"],
  "phone_number": "+1234567890",
  "email": "user@example.com",
  "trusted_devices": ["device1", "device2"]
}
```

#### PUT /api/user/{id}/settings
Update user authentication settings.

**Request Body:**
```json
{
  "otp_enabled": true,
  "otp_methods": ["sms", "email"],
  "phone_number": "+1234567890",
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "updated_fields": ["otp_enabled", "phone_number"]
}
```

#### POST /api/users/bulk_update
Bulk update user authentication settings.

**Request Body:**
```json
[
  {
    "id": 123,
    "otp_enabled": true,
    "phone_number": "+1234567890"
  },
  {
    "id": 124,
    "otp_enabled": false
  }
]
```

**Response:**
```json
{
  "success": true,
  "processed_count": 2,
  "successful_updates": 2,
  "failed_updates": 0
}
```

### Reporting Endpoints

#### GET /api/reports/login_stats
Get login statistics for a specified period.

**Query Parameters:**
- `start_date`: YYYY-MM-DD
- `end_date`: YYYY-MM-DD
- `format`: json|csv (default: json)

**Response:**
```json
{
  "period": {
    "start": "2023-01-01",
    "end": "2023-01-31"
  },
  "total_logins": 1250,
  "successful_authentications": 1200,
  "failed_attempts": 50,
  "otp_success_rate": 0.96
}
```

#### GET /api/reports/security_incidents
Get security incidents report.

**Query Parameters:**
- `start_date`: YYYY-MM-DD
- `end_date`: YYYY-MM-DD
- `severity`: low|medium|high|critical (default: all)

**Response:**
```json
{
  "incidents": [
    {
      "id": 1,
      "timestamp": "2023-01-15T10:30:00Z",
      "type": "multiple_failed_attempts",
      "severity": "medium",
      "user_id": 123,
      "ip_address": "192.168.1.1",
      "description": "5 failed login attempts from same IP"
    }
  ]
}
```

### System Configuration Endpoints

#### GET /api/config
Get current plugin configuration.

**Response:**
```json
{
  "otp_expiry_minutes": 5,
  "max_attempts": 3,
  "lockout_duration_minutes": 15,
  "rate_limit_requests_per_minute": 5,
  "enable_sms": true,
  "enable_email": true,
  "enable_totp": true
}
```

#### PUT /api/config
Update plugin configuration.

**Request Body:**
```json
{
  "otp_expiry_minutes": 10,
  "max_attempts": 5,
  "enable_sms": false
}
```

**Response:**
```json
{
  "success": true,
  "updated_config": {
    "otp_expiry_minutes": 10,
    "max_attempts": 5,
    "enable_sms": false
  }
}
```

## Error Handling

### HTTP Status Codes
- `200`: Success
- `400`: Bad Request - Invalid input data
- `401`: Unauthorized - Authentication required
- `403`: Forbidden - Insufficient privileges
- `404`: Not Found - Resource doesn't exist
- `429`: Too Many Requests - Rate limit exceeded
- `500`: Internal Server Error

### Error Response Format
```json
{
  "error": {
    "code": "INVALID_OTP",
    "message": "The provided OTP code is invalid",
    "details": {
      "attempted_code": "123456",
      "remaining_attempts": 2
    }
  }
}
```

## Webhook Events

The plugin can send webhook notifications for specific events:

### Available Events
- `login.attempted`: When a login attempt is made
- `login.successful`: When authentication succeeds
- `login.failed`: When authentication fails
- `otp.sent`: When an OTP is sent to user
- `account.locked`: When an account is locked
- `suspicious.activity`: When suspicious activity is detected

### Webhook Payload Example
```json
{
  "event": "login.successful",
  "timestamp": "2023-01-15T10:30:00Z",
  "data": {
    "user_id": 123,
    "username": "johndoe",
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "session_id": "session123"
  }
}
```

## SDKs and Libraries

### PHP SDK
```php
require_once('secureotp-sdk-php/autoload.php');

$client = new SecureOTP\Client([
    'base_url' => 'https://your-moodle-site.com',
    'api_key' => 'your-api-key'
]);

$response = $client->authenticate($username, $password);
```

### JavaScript SDK
```javascript
import SecureOTP from 'secureotp-js-sdk';

const client = new SecureOTP({
  baseUrl: 'https://your-moodle-site.com',
  apiKey: 'your-api-key'
});

const result = await client.authenticate(username, password);
```

## Testing API Endpoints

### Sandbox Environment
Use the sandbox environment for testing:
```
https://sandbox.your-moodle-site.com/auth/secureotp/classes/api/rest_controller.php
```

### Test Credentials
- Username: `testuser`
- Password: `testpass`
- OTP: `123456` (for testing purposes only)