#!/bin/bash
# Attendance Smoke Test - Employee & Admin Flows
# Tests attendance pages to ensure no schema errors after cleanup

echo "=== ATTENDANCE SMOKE TEST ==="
echo ""

# Employee Attendance Test
echo "=== EMPLOYEE ATTENDANCE FLOW ==="
# Login as employee
COOKIES="cookies_emp.txt"
rm -f "$COOKIES"

# Get login form for CSRF token
LOGIN_HTML=$(curl -s -c "$COOKIES" http://localhost:8000/login)
CSRF=$(echo "$LOGIN_HTML" | grep -oP 'name="_token" value="\K[^"]+')

# Login
curl -s -b "$COOKIES" -c "$COOKIES" -X POST http://localhost:8000/login \
  --data "_token=$CSRF&login=employee1@rshdi.com&password=password" \
  -o /dev/null

# Access employee attendance page
ATT_RESPONSE=$(curl -s -w "\n%{http_code}" -b "$COOKIES" http://localhost:8000/employee/attendance)
HTTP_CODE=$(echo "$ATT_RESPONSE" | tail -1)
PAGE_CONTENT=$(echo "$ATT_RESPONSE" | head -1)

echo "HTTP Status: $HTTP_CODE"

# Check for errors
if echo "$PAGE_CONTENT" | grep -q 'notifiable_type\|SQLSTATE\|Field.*default'; then
    echo "✗ FOUND SCHEMA ERROR in employee page"
else
    echo "✓ No schema errors in employee attendance page"
fi

# Admin Attendance Test
echo ""
echo "=== ADMIN ATTENDANCE FLOW ==="
COOKIES2="cookies_admin.txt"
rm -f "$COOKIES2"

# Get login form
LOGIN_HTML2=$(curl -s -c "$COOKIES2" http://localhost:8000/login)
CSRF2=$(echo "$LOGIN_HTML2" | grep -oP 'name="_token" value="\K[^"]+')

# Login as superadmin
curl -s -b "$COOKIES2" -c "$COOKIES2" -X POST http://localhost:8000/login \
  --data "_token=$CSRF2&login=superadmin@rshdi.com&password=password" \
  -o /dev/null

# Access admin attendance page
ATT_ADMIN=$(curl -s -w "\n%{http_code}" -b "$COOKIES2" http://localhost:8000/attendances)
HTTP_CODE2=$(echo "$ATT_ADMIN" | tail -1)
PAGE_CONTENT2=$(echo "$ATT_ADMIN" | head -1)

echo "HTTP Status: $HTTP_CODE2"

# Check for errors
if echo "$PAGE_CONTENT2" | grep -q 'notifiable_type\|SQLSTATE\|Field.*default'; then
    echo "✗ FOUND SCHEMA ERROR in admin page"
else
    echo "✓ No schema errors in admin attendance page"
fi

# Cleanup
rm -f "$COOKIES" "$COOKIES2"

echo ""
echo "=== TEST COMPLETE ==="
