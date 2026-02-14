#!/bin/bash
# Error checking script for SecureOTP plugin

echo "=== Checking for common errors in SecureOTP plugin ==="
echo ""

ERRORS=0

# Check 1: Undefined methods
echo "[1/6] Checking for undefined method calls..."
if grep -r "sanitize_identifier\|check_rate_limit\|generate_otp\|validate_otp" --include="*.php" . | grep -v "function " | grep -v "public function" > /dev/null; then
    echo "✓ Methods appear to be called correctly"
else
    echo "⚠ Potential undefined method calls found"
    ERRORS=$((ERRORS + 1))
fi

# Check 2: Global variables
echo "[2/6] Checking global variable declarations..."
if grep -l "global \$CFG.*\$PAGE.*\$OUTPUT" login.php verify_otp.php > /dev/null; then
    echo "✓ Global variables declared in login pages"
else
    echo "✗ Missing global variable declarations"
    ERRORS=$((ERRORS + 1))
fi

# Check 3: Database table references
echo "[3/6] Checking database table names..."
if grep -r "auth_secureotp_audit_log" --include="*.php" . > /dev/null; then
    echo "✗ Found incorrect table name 'auth_secureotp_audit_log' (should be 'auth_secureotp_audit')"
    ERRORS=$((ERRORS + 1))
else
    echo "✓ Database table names look correct"
fi

# Check 4: Language strings
echo "[4/6] Checking for language string usage..."
if [ -f "lang/en/auth_secureotp.php" ]; then
    echo "✓ English language file exists"
else
    echo "✗ Missing English language file"
    ERRORS=$((ERRORS + 1))
fi

# Check 5: Required classes
echo "[5/6] Checking for required class files..."
REQUIRED_CLASSES=(
    "classes/security/input_sanitizer.php"
    "classes/security/csrf_protection.php"
    "classes/security/audit_logger.php"
    "classes/auth/otp_manager.php"
    "classes/auth/rate_limiter.php"
    "classes/auth/device_fingerprint.php"
    "classes/messaging/sms_gateway.php"
    "classes/messaging/twilio_gateway.php"
)

MISSING=0
for class_file in "${REQUIRED_CLASSES[@]}"; do
    if [ ! -f "$class_file" ]; then
        echo "  ✗ Missing: $class_file"
        MISSING=$((MISSING + 1))
    fi
done

if [ $MISSING -eq 0 ]; then
    echo "✓ All required classes present"
else
    echo "✗ Missing $MISSING required class files"
    ERRORS=$((ERRORS + 1))
fi

# Check 6: Templates
echo "[6/6] Checking for template files..."
if [ -f "templates/login.mustache" ] && [ -f "templates/otp_verify.mustache" ]; then
    echo "✓ Template files exist"
else
    echo "✗ Missing template files"
    ERRORS=$((ERRORS + 1))
fi

echo ""
echo "=== Summary ==="
if [ $ERRORS -eq 0 ]; then
    echo "✓ No errors found! Plugin should work correctly."
    exit 0
else
    echo "✗ Found $ERRORS potential issues. Please review above."
    exit 1
fi
