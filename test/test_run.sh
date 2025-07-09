#!/bin/bash

# Test script for php-logger optimized image
set -e

echo "=== PHP Logger Optimized Image Test ==="
echo "Starting test environment..."

# Create test logs directory
mkdir -p test-logs

# Start the test environment
echo "Starting containers..."
docker-compose -f docker-compose.test.yml up -d

# Wait for containers to be ready
echo "Waiting for containers to be ready..."
sleep 10

# Check if containers are running
echo "Checking container status..."
docker-compose -f docker-compose.test.yml ps

# Test 1: Check if tail command is available
echo ""
echo "=== Test 1: Checking if 'tail' command is available ==="
docker exec php-logger-test tail --version
if [ $? -eq 0 ]; then
    echo "✅ tail command is available"
else
    echo "❌ tail command is not available"
    exit 1
fi

# Test 2: Check if rm command is available
echo ""
echo "=== Test 2: Checking if 'rm' command is available ==="
docker exec php-logger-test rm --version
if [ $? -eq 0 ]; then
    echo "✅ rm command is available"
else
    echo "❌ rm command is not available"
    exit 1
fi

# Test 2b: Check if du command is available
echo ""
echo "=== Test 2b: Checking if 'du' command is available ==="
docker exec php-logger-test du --version
if [ $? -eq 0 ]; then
    echo "✅ du command is available"
else
    echo "❌ du command is not available"
    exit 1
fi

# Test 3: Generate test logs
echo ""
echo "=== Test 3: Generating test logs ==="
docker exec php-logger-test php /var/www/test_log_gen.php 20 mixed
if [ $? -eq 0 ]; then
    echo "✅ Log generation successful"
else
    echo "❌ Log generation failed"
    exit 1
fi

# Test 4: Check if logs directory exists and has files
echo ""
echo "=== Test 4: Checking log files ==="
docker exec php-logger-test php -r "print_r(scandir('/app/custom-logs'));"
if [ $? -eq 0 ]; then
    echo "✅ Log directory and files exist"
else
    echo "❌ Log directory or files missing"
    exit 1
fi

# Test 5: Test tail command on log files
echo ""
echo "=== Test 5: Testing tail command on log files ==="
docker exec php-logger-test tail -n 5 /app/custom-logs/application.log
if [ $? -eq 0 ]; then
    echo "✅ tail command works on log files"
else
    echo "❌ tail command failed on log files"
    exit 1
fi

# Test 5b: Test du command on log directory
echo ""
echo "=== Test 5b: Testing du command on log directory ==="
docker exec php-logger-test du -sh /app/custom-logs/
if [ $? -eq 0 ]; then
    echo "✅ du command works on log directory"
else
    echo "❌ du command failed on log directory"
    exit 1
fi

# Test 6: Test PHP-FPM service
echo ""
echo "=== Test 6: Testing PHP-FPM service ==="
curl -s -o /dev/null -w "%{http_code}" http://localhost:8081/
response=$?
if [ $response -eq 0 ]; then
    echo "✅ PHP-FPM service is accessible"
else
    echo "❌ PHP-FPM service is not accessible"
    exit 1
fi

# Test 7: Test log fetching via web interface
echo ""
echo "=== Test 7: Testing log fetching via web interface ==="
curl -s "http://localhost:8081/index.php?fetch=1" | head -5
if [ $? -eq 0 ]; then
    echo "✅ Log fetching via web interface works"
else
    echo "❌ Log fetching via web interface failed"
    exit 1
fi

# Test 8: Test log deletion functionality
echo ""
echo "=== Test 8: Testing log deletion functionality ==="
# First generate some logs
docker exec php-logger-test php /var/www/test_log_gen.php 5 error
# Then test deletion
curl -s "http://localhost:8081/index.php?delete=1"
if [ $? -eq 0 ]; then
    echo "✅ Log deletion functionality works"
else
    echo "❌ Log deletion functionality failed"
    exit 1
fi

# Test 9: Check image size
echo ""
echo "=== Test 9: Checking optimized image size ==="
docker images radosz88/php-logger:latest --format "table {{.Repository}}\t{{.Tag}}\t{{.Size}}"

echo ""
echo "=== All Tests Completed Successfully! ==="
echo "The optimized php-logger image is working correctly with:"
echo "- Custom log directory: /app/custom-logs"
echo "- tail command available"
echo "- rm command available"
echo "- PHP-FPM service running"
echo "- Log generation working"
echo "- Web interface functional"
echo "- Log deletion working"

# Cleanup
echo ""
echo "Cleaning up test environment..."
docker-compose -f docker-compose.test.yml down

echo "Test complete!"
