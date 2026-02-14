# SecureOTP Deployment Guide

## Pre-deployment Checklist

### System Requirements
- [ ] Moodle 3.9 or higher
- [ ] PHP 7.4 or higher with required extensions
- [ ] Sufficient disk space for logs and temporary files
- [ ] Network connectivity to SMS/email gateways (if using)
- [ ] Redis server (recommended for production)
- [ ] SSL certificate for HTTPS

### Security Preparations
- [ ] Review security configurations
- [ ] Set up monitoring and alerting
- [ ] Prepare incident response procedures
- [ ] Backup current authentication system
- [ ] Plan rollback procedures

## Production Deployment Steps

### 1. Environment Preparation
```bash
# Ensure all prerequisites are met
php -v
moodle/checks.php
redis-cli ping
```

### 2. Plugin Installation
```bash
# Copy plugin files to Moodle installation
cp -r /path/to/secureotp /var/www/moodle/auth/

# Set proper file permissions
chown -R www-data:www-data /var/www/moodle/auth/secureotp
chmod -R 755 /var/www/moodle/auth/secureotp
```

### 3. Database Setup
The plugin will automatically create required tables during installation via Moodle's upgrade process. Monitor the installation for any errors.

### 4. Configuration
Access the plugin configuration at:
Site Administration > Plugins > Authentication > SecureOTP

Configure the following settings:
- OTP expiry time
- Retry limits
- Lockout duration
- SMS/email gateway settings
- Rate limiting parameters

### 5. Testing
Before enabling for all users:
- Test authentication flow with test accounts
- Verify SMS/email delivery
- Check rate limiting functionality
- Validate session management
- Test administrative controls

### 6. Gradual Rollout
1. Enable for test group first
2. Monitor logs and performance
3. Gather feedback from test users
4. Address any issues
5. Gradually expand to more users
6. Full rollout after validation

## Configuration Files

### Main Configuration (`config.php`)
Located at `/auth/secureotp/config.php`, contains environment-specific settings:

```php
<?php
// Production settings
$CFG->secureotp_sms_provider = 'twilio';
$CFG->secureotp_redis_host = 'redis.example.com';
$CFG->secureotp_log_level = 'WARNING';
?>
```

### Environment Variables
Set these environment variables for sensitive configuration:

```
SECUREOTP_SMS_API_KEY=your_sms_api_key
SECUREOTP_SMS_SECRET=your_sms_secret
SECUREOTP_ENCRYPTION_KEY=your_encryption_key
SECUREOTP_REDIS_PASSWORD=your_redis_password
```

## Monitoring and Maintenance

### Key Metrics to Monitor
- Authentication success/failure rates
- OTP delivery success rates
- System resource utilization
- Error log frequency
- Rate limiting triggers

### Log Management
- Rotate logs daily to prevent disk space issues
- Archive logs for compliance requirements
- Monitor logs for security incidents
- Set up alerts for unusual patterns

### Regular Maintenance Tasks
- Clean up expired OTP records
- Update encryption keys periodically
- Review and update SMS/email provider credentials
- Perform security audits
- Update to latest plugin version

## Scaling Considerations

### Horizontal Scaling
- Use Redis for shared session storage
- Load balance across multiple Moodle instances
- Use CDN for static assets
- Implement database read replicas if needed

### Performance Optimization
- Cache frequently accessed data
- Optimize database queries
- Use asynchronous processing for non-critical operations
- Implement connection pooling

## Troubleshooting

### Common Issues
1. **OTP not being sent**
   - Check SMS/email gateway credentials
   - Verify network connectivity
   - Review rate limiting settings

2. **Authentication failures**
   - Check encryption key configuration
   - Verify database connectivity
   - Review session settings

3. **Performance issues**
   - Monitor database query performance
   - Check Redis connectivity and performance
   - Review server resource utilization

### Diagnostic Commands
```bash
# Check plugin status
php admin/cli/checks.php --component=auth_secureotp

# Run plugin-specific tests
php vendor/bin/phpunit --testsuite=auth_secureotp

# Check configuration
php auth/secureotp/cli/diagnostic.php
```

## Rollback Procedures

### In Case of Critical Issues
1. Disable the plugin from Moodle admin panel
2. Switch back to previous authentication method
3. Restore from backup if necessary
4. Investigate and fix the issue
5. Redeploy after validation

### Backup Commands
```bash
# Backup plugin configuration
mysqldump moodle_db mdl_auth_oauth2_provider > backup_oauth.sql

# Backup plugin files
tar -czf secureotp_backup.tar.gz /var/www/moodle/auth/secureotp
```

## Post-deployment Validation

### Verification Steps
- [ ] Successful authentication with OTP
- [ ] Proper error handling
- [ ] Correct logging of events
- [ ] Rate limiting functionality
- [ ] Session management working correctly
- [ ] Administrative controls accessible
- [ ] Reports generating correctly

### Performance Benchmarks
- Authentication response time < 2 seconds
- OTP delivery time < 10 seconds
- System resource usage within acceptable limits
- Concurrent user capacity meets requirements

## Security Hardening

### Recommended Settings
- Enforce HTTPS for all authentication pages
- Implement CAPTCHA for public-facing login
- Regularly rotate encryption keys
- Monitor for suspicious authentication patterns
- Implement IP whitelisting if appropriate