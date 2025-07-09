# Test Directory

This directory contains all testing utilities and configurations for the PHP Logger project.

## Files Overview

### Test Scripts
- **`final_test.sh`** - Comprehensive test that verifies all functionality
- **`test_run.sh`** - Extended test suite with multiple test scenarios
- **`test_log_gen.php`** - PHP script to generate test log entries

### Test Configuration
- **`docker-compose.test.yml`** - Docker Compose configuration for testing
- **`nginx-test.conf`** - Nginx configuration for web server testing

### Test Data
- **`test-logs/`** - Directory for test log files (created during tests)

## Running Tests

### Quick Verification Test
```bash
./final_test.sh
```
This runs a comprehensive test to verify:
- Image size optimization
- Command availability (tail, rm, du)
- File inclusion (index.php, profiles.php, test_log_gen.php)
- Log generation functionality
- Directory size calculation
- File operations

### Extended Test Suite
```bash
./test_run.sh
```
This runs a more detailed test suite including:
- Individual command tests
- Web interface testing
- Log deletion functionality
- Custom log directory testing
- Multi-container setup testing

### Manual Testing
```bash
# Start test environment
docker-compose -f docker-compose.test.yml up -d

# Generate test logs
docker exec php-logger-test php /var/www/test_log_gen.php 10 mixed

# Test profiles.php functionality
docker exec php-logger-test php -r "
\$_GET['space'] = '1';
include '/var/www/profiles.php';
"

# Stop test environment
docker-compose -f docker-compose.test.yml down
```

## Test Configuration Details

### docker-compose.test.yml
- **PHP-FPM Container**: Uses optimized `radosz88/php-logger:latest`
- **Nginx Container**: Serves PHP files via FastCGI
- **Custom Log Directory**: Uses `/app/custom-logs` for testing
- **Port Mapping**: PHP-FPM on 8080, Nginx on 8081

### nginx-test.conf
- Configured for PHP-FPM integration
- Proper FastCGI parameter passing
- Support for query parameters (needed for profiles.php)

## Test Log Generator

The `test_log_gen.php` script generates realistic log entries with:
- **Log Types**: info, warning, error, debug
- **Multiple Files**: application.log, error.log, access.log, debug.log
- **Realistic Content**: Timestamps, IPs, user IDs, varied messages
- **Configurable**: Number of entries and log type

Usage:
```bash
php test_log_gen.php [count] [type]
# Examples:
php test_log_gen.php 20 mixed    # 20 mixed-type entries
php test_log_gen.php 10 error    # 10 error entries
php test_log_gen.php 5 info      # 5 info entries
```

## Expected Test Results

### Successful Test Output
```
✅ Image size: 73.7MB
✅ All required commands available: tail, rm, du
✅ All required files present: index.php, profiles.php, test_log_gen.php
✅ Log generation working
✅ profiles.php directory size calculation working
✅ Log file operations working
✅ File deletion working
✅ Directory usage calculation working
```

### Key Functionality Verification
- **Directory Size**: `profiles.php` returns format like `16K /app/custom-logs/`
- **Command Availability**: All commands return proper version info
- **File Operations**: tail, rm, du work correctly on log files
- **Log Generation**: Creates multiple log files with realistic content

## Troubleshooting

### Common Issues
1. **Port Conflicts**: Ensure ports 8080 and 8081 are available
2. **Docker Permissions**: Make sure Docker is running and accessible
3. **File Missing**: If profiles.php is missing, rebuild with `../deploy.sh`

### Test Environment Cleanup
```bash
# Stop all test containers
docker-compose -f docker-compose.test.yml down

# Remove test volumes
docker volume prune

# Clean up test logs
rm -rf test-logs/
```

## Integration with Main Project

These tests are designed to work with the main project's deployment process:
1. Run `../deploy.sh` to build and optimize the image
2. Run tests to verify functionality
3. Deploy to production if all tests pass

The tests validate that the optimized image maintains all required functionality while being significantly smaller than the original.
