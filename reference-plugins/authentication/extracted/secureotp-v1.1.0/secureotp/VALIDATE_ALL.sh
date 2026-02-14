#!/bin/bash
# Comprehensive validation of SecureOTP plugin

echo "=========================================="
echo "  SecureOTP Plugin - Full Validation"
echo "=========================================="
echo ""

ERRORS=0
WARNINGS=0

# Check 1: Syntax errors
echo "[1/8] Checking PHP syntax..."
for file in auth.php login.php verify_otp.php classes/**/*.php; do
    if [ -f "$file" ]; then
        result=$(php -l "$file" 2>&1)
        if [[ $result != *"No syntax errors"* ]]; then
            echo "  ✗ Syntax error in $file"
            echo "    $result"
            ERRORS=$((ERRORS + 1))
        fi
    fi
done
if [ $ERRORS -eq 0 ]; then
    echo "  ✓ All PHP files have valid syntax"
fi

# Check 2: Database table references
echo ""
echo "[2/8] Checking database table references..."
VALID_TABLES=(
    "auth_secureotp_userdata"
    "auth_secureotp_security"
    "auth_secureotp_audit"
    "auth_secureotp_import_log"
    "auth_secureotp_rate_limit"
)

INVALID_TABLES=(
    "auth_secureotp_otps"
    "auth_secureotp_audit_log"
    "auth_secureotp_rate_limits"
)

for table in "${INVALID_TABLES[@]}"; do
    if grep -r "$table" --include="*.php" . | grep -v "VALIDATE_ALL" > /dev/null; then
        echo "  ✗ Found reference to invalid table: $table"
        grep -r "$table" --include="*.php" . | grep -v "VALIDATE_ALL" | head -3
        ERRORS=$((ERRORS + 1))
    fi
done

if [ $ERRORS -eq 0 ]; then
    echo "  ✓ All table references are valid"
fi

# Check 3: Method existence
echo ""
echo "[3/8] Checking critical method implementations..."

CRITICAL_METHODS=(
    "input_sanitizer:sanitize_identifier"
    "rate_limiter:check_rate_limit"
    "rate_limiter:record_attempt"
    "otp_manager:generate_otp"
    "otp_manager:validate_otp"
    "csrf_protection:verify_token"
    "device_fingerprint:get_fingerprint"
)

for item in "${CRITICAL_METHODS[@]}"; do
    class="${item%%:*}"
    method="${item##*:}"

    if ! grep -r "function $method" classes/ --include="*.php" | grep "$class" > /dev/null; then
        echo "  ✗ Missing method: $class::$method()"
        ERRORS=$((ERRORS + 1))
    fi
done

if [ $ERRORS -eq 0 ]; then
    echo "  ✓ All critical methods exist"
fi

# Check 4: Global variable declarations
echo ""
echo "[4/8] Checking global variable declarations..."
for file in login.php verify_otp.php; do
    if ! grep -q "global.*CFG.*PAGE.*OUTPUT" "$file"; then
        echo "  ✗ Missing global declarations in $file"
        ERRORS=$((ERRORS + 1))
    fi
done

if [ $ERRORS -eq 0 ]; then
    echo "  ✓ Global variables properly declared"
fi

# Check 5: Language strings
echo ""
echo "[5/8] Checking language files..."
if [ ! -f "lang/en/auth_secureotp.php" ]; then
    echo "  ✗ Missing English language file"
    ERRORS=$((ERRORS + 1))
else
    # Check for required strings
    REQUIRED_STRINGS=(
        "login_title"
        "otp_title"
        "send_otp"
        "verify_otp"
        "error_invalid_otp"
    )

    for string in "${REQUIRED_STRINGS[@]}"; do
        if ! grep -q "\$string\['$string'\]" lang/en/auth_secureotp.php; then
            echo "  ⚠ Missing language string: $string"
            WARNINGS=$((WARNINGS + 1))
        fi
    done
fi

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo "  ✓ Language files complete"
fi

# Check 6: Template files
echo ""
echo "[6/8] Checking template files..."
if [ ! -f "templates/login.mustache" ]; then
    echo "  ✗ Missing login template"
    ERRORS=$((ERRORS + 1))
fi

if [ ! -f "templates/otp_verify.mustache" ]; then
    echo "  ✗ Missing OTP verification template"
    ERRORS=$((ERRORS + 1))
fi

if [ $ERRORS -eq 0 ]; then
    echo "  ✓ All required templates exist"
fi

# Check 7: Database schema fields
echo ""
echo "[7/8] Checking database schema consistency..."

# Check if code references match schema
if grep -r "otp_hash" classes/auth/otp_manager.php > /dev/null; then
    echo "  ✗ Found 'otp_hash' but schema uses 'current_otp_hash'"
    ERRORS=$((ERRORS + 1))
fi

if [ $ERRORS -eq 0 ]; then
    echo "  ✓ Database field references consistent"
fi

# Check 8: Return value consistency
echo ""
echo "[8/8] Checking method return values..."

# Check if validate_otp returns array
if ! grep -A5 "function validate_otp" classes/auth/otp_manager.php | grep -q "return array"; then
    echo "  ⚠ validate_otp may not return array format"
    WARNINGS=$((WARNINGS + 1))
fi

# Check if generate_otp returns array
if ! grep -A5 "function generate_otp" classes/auth/otp_manager.php | grep -q "return array"; then
    echo "  ⚠ generate_otp may not return array format"
    WARNINGS=$((WARNINGS + 1))
fi

if [ $WARNINGS -eq 0 ]; then
    echo "  ✓ Method return values consistent"
fi

# Summary
echo ""
echo "=========================================="
echo "  Validation Complete"
echo "=========================================="
echo ""
echo "Errors: $ERRORS"
echo "Warnings: $WARNINGS"
echo ""

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo "✓ ALL CHECKS PASSED - PRODUCTION READY!"
    exit 0
elif [ $ERRORS -eq 0 ]; then
    echo "⚠ Passed with warnings - Review recommended"
    exit 0
else
    echo "✗ FAILED - Please fix errors above"
    exit 1
fi
