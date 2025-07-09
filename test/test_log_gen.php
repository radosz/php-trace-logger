<?php
// Test log generator script
// This script generates various types of log entries for testing purposes

// Get logs directory from environment variable or use default
$logs_dir = getenv('LOGS_DIR') ?: '/var/www/html/logs';

// Ensure logs directory exists
if (!is_dir($logs_dir)) {
    mkdir($logs_dir, 0777, true);
}

// Function to generate a random log entry
function generateLogEntry($type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $messages = [
        'info' => [
            'User login successful',
            'Database connection established',
            'Cache cleared successfully',
            'Configuration loaded',
            'Request processed successfully'
        ],
        'warning' => [
            'High memory usage detected',
            'Slow query detected',
            'Cache miss ratio high',
            'Connection timeout warning',
            'Rate limit approaching'
        ],
        'error' => [
            'Database connection failed',
            'File not found',
            'Permission denied',
            'Authentication failed',
            'Network timeout error'
        ],
        'debug' => [
            'Function call trace',
            'Variable dump',
            'SQL query execution',
            'API response received',
            'Memory allocation info'
        ]
    ];
    
    $message = $messages[$type][array_rand($messages[$type])];
    $ip = '192.168.' . rand(1, 255) . '.' . rand(1, 255);
    $user_id = rand(1000, 9999);
    
    return "[{$timestamp}] [{$type}] {$message} (IP: {$ip}, User: {$user_id})";
}

// Generate logs based on command line arguments or default behavior
$log_count = isset($argv[1]) ? (int)$argv[1] : 10;
$log_type = isset($argv[2]) ? $argv[2] : 'mixed';

echo "Generating {$log_count} log entries of type '{$log_type}' to {$logs_dir}\n";

// Create different log files
$log_files = [
    'application.log',
    'error.log',
    'access.log',
    'debug.log'
];

for ($i = 0; $i < $log_count; $i++) {
    $file = $log_files[array_rand($log_files)];
    $log_path = $logs_dir . '/' . $file;
    
    if ($log_type === 'mixed') {
        $types = ['info', 'warning', 'error', 'debug'];
        $type = $types[array_rand($types)];
    } else {
        $type = $log_type;
    }
    
    $log_entry = generateLogEntry($type);
    
    // Append to log file
    file_put_contents($log_path, $log_entry . "\n", FILE_APPEND | LOCK_EX);
    
    echo "Generated: {$log_entry}\n";
    
    // Small delay to make timestamps different
    usleep(100000); // 0.1 second
}

echo "\nLog generation complete!\n";
echo "Files created in: {$logs_dir}\n";

// List generated files
$files = glob($logs_dir . '/*.log');
foreach ($files as $file) {
    $lines = count(file($file));
    echo "- " . basename($file) . " ({$lines} lines)\n";
}
?>
