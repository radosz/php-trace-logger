<?php
if (isset($_GET['space'])) {
    // Get logs directory from environment variable or use default
    $logs_dir = getenv('LOGS_DIR') ?: '/var/www/html/logs';
    $space = shell_exec('du -sh ' . escapeshellarg($logs_dir) . '/');
    echo $space;
    exit;
}
?>