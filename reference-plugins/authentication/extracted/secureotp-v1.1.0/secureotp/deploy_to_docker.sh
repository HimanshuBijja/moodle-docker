#!/bin/bash
# Deploy SecureOTP plugin to Docker container

CONTAINER="cyber_academy_9000_app"
SOURCE_DIR="/Users/vamanakhil/Documents/TGCSB/TGPA/Project-R/Authentication_Plugins/moodle/auth/secureotp"
DEST_DIR="/var/www/html/auth/secureotp"

echo "=== Deploying SecureOTP Plugin to Docker ==="
echo ""

# Critical files that were recently fixed
CRITICAL_FILES=(
    "login.php"
    "verify_otp.php"
    "auth.php"
    "classes/security/input_sanitizer.php"
    "classes/security/audit_logger.php"
    "classes/import/user_importer.php"
)

echo "[1/2] Copying critical fixed files..."
for file in "${CRITICAL_FILES[@]}"; do
    echo "  → $file"
    sudo docker cp "$SOURCE_DIR/$file" "$CONTAINER:$DEST_DIR/$file"
done

echo ""
echo "[2/2] Setting permissions..."
sudo docker exec $CONTAINER chown -R www-data:www-data $DEST_DIR/

echo ""
echo "✓ Deployment complete!"
echo ""
echo "Next steps:"
echo "1. Access login page: http://your-site/auth/secureotp/login.php"
echo "2. Configure Twilio in admin panel"
echo "3. Test with a user from the imported CSV"
