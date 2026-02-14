#!/bin/bash
# FINAL DEPLOYMENT - All Fixed Files for SecureOTP Plugin

set -e  # Exit on error

CONTAINER="cyber_academy_9000_app"
SOURCE="/Users/vamanakhil/Documents/TGCSB/TGPA/Project-R/Authentication_Plugins/moodle/auth/secureotp"
DEST="/var/www/html/auth/secureotp"

echo "========================================"
echo "  SecureOTP Plugin - FINAL DEPLOYMENT  "
echo "========================================"
echo ""

# All files that were fixed
FIXED_FILES=(
    # Core pages
    "login.php:Fixed \$SITE->fullname and logo handling"
    "verify_otp.php:Fixed \$SITE->fullname and logo handling"
    "auth.php:Core authentication logic"

    # Security classes
    "classes/security/input_sanitizer.php:Added sanitize_identifier() method"
    "classes/security/csrf_protection.php:Added verify_token() alias"
    "classes/security/audit_logger.php:Fixed schema matching and table name"

    # Auth classes
    "classes/auth/rate_limiter.php:Added check_rate_limit() and record_attempt()"
    "classes/auth/device_fingerprint.php:Added get_fingerprint() alias"

    # Import classes
    "classes/import/user_importer.php:Fixed transactions and username lowercase"
)

echo "[1/3] Copying all fixed files..."
echo ""

for item in "${FIXED_FILES[@]}"; do
    file="${item%%:*}"
    desc="${item##*:}"

    if [ -f "$SOURCE/$file" ]; then
        echo "  ✓ $file"
        echo "    → $desc"
        sudo docker cp "$SOURCE/$file" "$CONTAINER:$DEST/$file"
    else
        echo "  ✗ $file (NOT FOUND!)"
        exit 1
    fi
done

echo ""
echo "[2/3] Setting correct permissions..."
sudo docker exec $CONTAINER chown -R www-data:www-data $DEST/

echo ""
echo "[3/3] Clearing PHP opcache..."
sudo docker exec $CONTAINER php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared\n'; }"

echo ""
echo "========================================"
echo "  ✓ DEPLOYMENT COMPLETE!  "
echo "========================================"
echo ""
echo "Fixed methods:"
echo "  ✓ sanitize_identifier()"
echo "  ✓ check_rate_limit()"
echo "  ✓ record_attempt()"
echo "  ✓ verify_token()"
echo "  ✓ get_fingerprint()"
echo "  ✓ Transaction handling"
echo "  ✓ Username lowercase"
echo "  ✓ Global variables"
echo "  ✓ Logo null check"
echo ""
echo "Next steps:"
echo "1. Visit: http://localhost/auth/secureotp/login.php"
echo "2. Enter: mh001234 (or any imported employee ID)"
echo "3. Check for SMS delivery"
echo ""
echo "No more errors expected! 🎉"
