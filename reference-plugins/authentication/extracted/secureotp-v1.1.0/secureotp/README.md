# Secure OTP Authentication Plugin for Moodle

[![Moodle](https://img.shields.io/badge/Moodle-4.5%2B-orange.svg)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-GPLv3-green.svg)](https://www.gnu.org/licenses/gpl-3.0)

**Production-ready OTP authentication for government organizations** - Built for the Government of India Training Portal serving 80,000+ users.

## 🎯 Overview

SecureOTP is a government-certified authentication plugin for Moodle that replaces traditional passwords with secure one-time passwords (OTP) delivered via SMS or Email. Designed for large-scale deployments with multi-layer security, comprehensive audit logging, and compliance with government regulations.

### Key Features

✅ **Passwordless Authentication** - Login with Employee ID + OTP only
✅ **Multi-Channel OTP Delivery** - SMS (Twilio) with Email fallback
✅ **Bulk User Import** - Import 80,000+ users from CSV in minutes
✅ **Multi-Language Support** - English, Hindi (हिन्दी), Telugu (తెలుగు)
✅ **Enterprise Security** - Rate limiting, device fingerprinting, CSRF protection
✅ **Immutable Audit Logs** - 7-year retention with HMAC signatures
✅ **Admin Dashboard** - Real-time security monitoring & user management
✅ **Moodle 4.5+ Compatible** - Full compatibility with latest Moodle

---

## 📋 Requirements

### System Requirements

- **Moodle**: 4.5 or higher
- **PHP**: 8.0 or higher
- **Database**: PostgreSQL 12+ or MySQL 8.0+ (PostgreSQL recommended for audit partitioning)
- **Redis**: 6.0+ (required for OTP storage and message queuing)
- **Memory**: 512MB+ PHP memory limit (1GB+ for bulk imports)

### PHP Extensions

- `redis` - For OTP caching and message queue
- `curl` - For Twilio API calls
- `json` - For data serialization
- `mbstring` - For multi-language support
- `openssl` - For encryption and signatures

### External Services

- **Twilio Account** (for SMS OTP) - Get credentials from [twilio.com](https://www.twilio.com)
- **SMTP Server** (for email OTP fallback) - Configure in Moodle

---

## 🚀 Installation

### Step 1: Download & Extract

```bash
cd /path/to/moodle/auth/
git clone https://github.com/your-org/moodle-auth-secureotp.git secureotp
# OR
unzip auth_secureotp.zip -d secureotp
```

### Step 2: Install Database Schema

```bash
cd /path/to/moodle
php admin/cli/upgrade.php
```

This creates 5 custom tables:
- `mdl_auth_secureotp_userdata` - Extended user profiles
- `mdl_auth_secureotp_security` - Authentication metadata
- `mdl_auth_secureotp_audit` - Immutable audit logs
- `mdl_auth_secureotp_import_log` - Bulk import tracking
- `mdl_auth_secureotp_rate_limit` - Rate limiting (DB fallback)

### Step 3: Configure Redis

Edit `config.php`:

```php
$CFG->auth_secureotp_redis_host = '127.0.0.1';
$CFG->auth_secureotp_redis_port = 6379;
$CFG->auth_secureotp_redis_password = 'your_redis_password';
$CFG->auth_secureotp_redis_db = 0;
```

### Step 4: Configure Twilio (SMS Gateway)

Go to **Site Administration → Plugins → Authentication → Secure OTP**

Configure:
- **Twilio Account SID**: Your Twilio Account SID
- **Twilio Auth Token**: Your Twilio Auth Token
- **Twilio From Number**: Your Twilio phone number (E.164 format, e.g., +919876543210)

Test connection:
```bash
php auth/secureotp/cli/test_sms.php --mobile=9876543210 --message="Test"
```

### Step 5: Enable Plugin

Go to **Site Administration → Plugins → Authentication → Manage authentication**

- Click **Enable** next to Secure OTP
- Move Secure OTP to the top of the authentication methods list
- Save changes

---

## 👥 Bulk User Import

### CSV Format

Create a CSV file with the following columns:

**Required Fields:**
- `employee_id` - Unique employee/student ID
- `firstname` - First name
- `lastname` - Last name

**Optional Fields:**
- `email` - Email address (auto-generated if missing)
- `personal_mobile` - 10-digit mobile number
- `current_rank` - Current designation
- `working_location` - Office/location
- `date_of_birth` - Format: YYYY-MM-DD
- `date_of_joining` - Format: YYYY-MM-DD
- `gender` - M/F/O
- Plus 10 more HR-specific fields (see template)

### Generate Template

```bash
php auth/secureotp/cli/import_users.php --template > sample.csv
```

### Import Users

**Validate CSV first (dry run):**
```bash
php auth/secureotp/cli/import_users.php \
  --file=/data/employees.csv \
  --validate-only
```

**Import with preview:**
```bash
php auth/secureotp/cli/import_users.php \
  --file=/data/employees.csv \
  --dry-run
```

**Actual import:**
```bash
php auth/secureotp/cli/import_users.php \
  --file=/data/employees.csv \
  --source=HR_MASTER \
  --batch-size=100
```

**Import 80,000 users:** Takes approximately 30-60 minutes depending on hardware.

---

## 🔐 Security Features

### Rate Limiting

- **OTP Requests**: 3 per IP per 15 minutes
- **OTP Verification**: 5 attempts per user before account lock
- **Account Lockout**: 15 minutes (configurable)

### Device Fingerprinting

- Tracks user agent, screen resolution, timezone, language
- Logs device changes in audit trail
- Optional trusted device management (30-day expiry)

### Audit Logging

All events logged with:
- Event type, status, severity
- User ID and employee ID
- IP address and device fingerprint
- HMAC-SHA256 signature for tamper detection
- 7-year retention for compliance

### CSRF Protection

- All forms protected with session-based CSRF tokens
- Tokens validated on every submission
- Prevents cross-site request forgery attacks

---

## 📊 Admin Dashboard

Access: **Site Administration → Plugins → Authentication → Secure OTP → Dashboard**

### Metrics

- Total/Active/Locked users
- Today's logins and failed attempts
- Login success rate (last 24 hours)
- Real-time security alerts

### User Management

- Search by employee ID, name, mobile, email
- Filter by status (Active/Suspended/Locked)
- Bulk actions: Unlock, Suspend, Export
- View detailed audit logs per user
- Send test OTP

### Reports

- Security audit report (CSV/HTML)
- Compliance report with digital signature
- User activity report
- Custom date range queries

---

## 🔧 CLI Maintenance Scripts

### Sync Users with HR Database

```bash
php auth/secureotp/cli/sync_users.php --file=/data/latest_export.csv
```

### Cleanup Expired OTPs

Add to cron (run hourly):
```bash
0 * * * * php /path/to/moodle/auth/secureotp/cli/cleanup_otps.php
```

### Archive Audit Logs

Export logs older than 1 year:
```bash
php auth/secureotp/cli/audit_archive.php \
  --days=365 \
  --export=/backup/audit_logs/
```

### Test SMS Gateway

```bash
# Send test SMS
php auth/secureotp/cli/test_sms.php --mobile=9876543210

# Check Twilio balance
php auth/secureotp/cli/test_sms.php --check-balance
```

---

## 🌍 Multi-Language Support

### Available Languages

- **English** (en) - Default
- **Hindi** (हिन्दी) - Full translation
- **Telugu** (తెలుగు) - Full translation

### Add New Language

1. Copy `lang/en/auth_secureotp.php` to `lang/<langcode>/auth_secureotp.php`
2. Translate all strings
3. Test with `php admin/cli/purge_caches.php`

---

## 🧪 Testing

### Run PHPUnit Tests

```bash
cd /path/to/moodle
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit auth/secureotp/tests/
```

### Test Coverage

- `auth_test.php` - Authentication flow
- `otp_test.php` - OTP generation/validation
- `import_test.php` - CSV import & validation

---

## 📖 Configuration Options

### OTP Settings

- **OTP Length**: 4-8 digits (default: 6)
- **OTP Validity**: 1-10 minutes (default: 5)
- **OTP Algorithm**: SHA-256 based HMAC

### Security Settings

- **Max Login Attempts**: 3-10 (default: 5)
- **Lockout Duration**: 5-60 minutes (default: 15)
- **Rate Limit (OTP requests)**: 1-10 per hour (default: 3)
- **Enable Device Fingerprinting**: Yes/No
- **Require Trusted Device**: Yes/No

### Redis Settings

- **Host**: Redis server hostname (default: 127.0.0.1)
- **Port**: Redis server port (default: 6379)
- **Password**: Redis auth password (optional)
- **Database**: Redis DB number (default: 0)

---

## 🐛 Troubleshooting

### Common Issues

**Issue**: OTP not received via SMS
**Solution**:
- Check Twilio credentials in settings
- Run `php cli/test_sms.php --check-balance`
- Verify mobile number format (10 digits)
- Check Twilio logs at console.twilio.com

**Issue**: Redis connection failed
**Solution**:
- Verify Redis is running: `redis-cli ping`
- Check Redis credentials in config.php
- Test connection: `php -r "new Redis(); echo 'OK';"`

**Issue**: Import fails with memory error
**Solution**:
- Increase PHP memory: `php -d memory_limit=1G cli/import_users.php ...`
- Reduce batch size: `--batch-size=50`
- Import in smaller chunks

**Issue**: Users can't login
**Solution**:
- Verify user status: Check admin dashboard
- Unlock account if locked
- Check audit log for failed attempts
- Verify mobile number in user profile

---

## 📞 Support

### Getting Help

- **Documentation**: See `docs/` folder for detailed guides
- **Issue Tracker**: Report bugs via GitHub Issues
- **Email**: support@yourorg.gov.in

### Professional Support

For enterprise support, custom development, or training:
- Email: commercial@yourorg.gov.in
- Phone: +91-XXX-XXX-XXXX

---

## 📄 License

This plugin is licensed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.html).

```
SecureOTP Authentication Plugin for Moodle
Copyright © 2026 Government Training Institute

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
```

---

## 👨‍💻 Development

### Contributing

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch
3. Follow Moodle coding standards
4. Write PHPUnit tests
5. Submit a pull request

### Code Standards

- Follow [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle)
- Use PHPDoc comments
- Write meaningful commit messages
- Add tests for new features

---

## 🙏 Acknowledgments

- Built for the Government of India Training Portal
- Developed with support from [Your Organization]
- Special thanks to the Moodle community

---

## 📊 Version History

### v1.0.0 (2026-02-13)
- ✅ Initial release
- ✅ OTP authentication via SMS/Email
- ✅ Bulk user import (80K+ users)
- ✅ Multi-language support (EN/HI/TE)
- ✅ Admin dashboard & reports
- ✅ Moodle 4.5+ compatibility
- ✅ Comprehensive security features
- ✅ 7-year audit retention

---

**Made with ❤️ for Government of India** | **Serving 80,000+ users**
