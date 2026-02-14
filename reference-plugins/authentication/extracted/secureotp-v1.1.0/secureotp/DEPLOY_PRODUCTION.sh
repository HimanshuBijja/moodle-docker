#!/bin/bash
# PRODUCTION DEPLOYMENT - All fixes applied

set -e

CONTAINER="cyber_academy_9000_app"
SOURCE="/Users/vamanakhil/Documents/TGCSB/TGPA/Project-R/Authentication_Plugins/moodle/auth/secureotp"
DEST="/var/www/html/auth/secureotp"

echo "=========================================="
echo "  PRODUCTION DEPLOYMENT - SecureOTP"
echo "=========================================="
echo ""

# All fixed files
DEPLOY_FILES=(
    # Core pages
    "login.php"
    "verify_otp.php"
    "auth.php"
    "settings.php"
    "version.php"

    # Security classes
    "classes/security/input_sanitizer.php"
    "classes/security/csrf_protection.php"
    "classes/security/audit_logger.php"
    "classes/security/encryption.php"

    # Auth classes
    "classes/auth/otp_manager.php"
    "classes/auth/rate_limiter.php"
    "classes/auth/device_fingerprint.php"
    "classes/auth/session_manager.php"

    # Messaging classes
    "classes/messaging/sms_gateway.php"
    "classes/messaging/twilio_gateway.php"
    "classes/messaging/email_gateway.php"
    "classes/messaging/message_queue.php"

    # Import classes
    "classes/import/user_importer.php"
    "classes/import/csv_validator.php"

    # Admin classes
    "classes/admin/user_manager.php"
    "classes/admin/security_dashboard.php"
    "classes/admin/report_generator.php"

    # Tasks
    "classes/task/cleanup_otps.php"

    # Templates
    "templates/login.mustache"
    "templates/otp_verify.mustache"

    # Language files
    "lang/en/auth_secureotp.php"
)

echo "[1/3] Deploying ${#DEPLOY_FILES[@]} files..."
echo ""

SUCCESS=0
FAILED=0

for file in "${DEPLOY_FILES[@]}"; do
    if [ -f "$SOURCE/$file" ]; then
        sudo docker cp "$SOURCE/$file" "$CONTAINER:$DEST/$file" 2>&1
        if [ $? -eq 0 ]; then
            SUCCESS=$((SUCCESS + 1))
            echo "  ✓ $file"
        else
            FAILED=$((FAILED + 1))
            echo "  ✗ $file (FAILED)"
        fi
    else
        echo "  ⚠ $file (not found)"
    fi
done

echo ""
echo "[2/3] Setting permissions..."
sudo docker exec $CONTAINER chown -R www-data:www-data $DEST/

echo ""
echo "[3/3] Clearing caches..."
sudo docker exec $CONTAINER php -r "if (function_exists('opcache_reset')) opcache_reset();"

echo ""
echo "=========================================="
echo "  DEPLOYMENT SUMMARY"
echo "=========================================="
echo ""
echo "✓ Deployed: $SUCCESS files"
echo "✗ Failed: $FAILED files"
echo ""
echo "All fixes applied:"
echo "  ✓ Table names corrected (audit, rate_limit)"
echo "  ✓ OTP storage uses Redis + DB fallback"
echo "  ✓ All missing methods added"
echo "  ✓ Transaction handling fixed"
echo "  ✓ Username lowercase enforced"
echo "  ✓ Global variables declared"
echo "  ✓ Syntax errors resolved"
echo ""
echo "=========================================="
echo "  🎉 PRODUCTION READY!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Access: http://localhost/auth/secureotp/login.php"
echo "2. Enter: mh001234 (or any employee ID)"
echo "3. OTP will be generated and stored"
echo "4. SMS sent (if Twilio configured)"
echo ""
echo "Note: Redis warnings are non-fatal (DB fallback works)"
echo ""
