<?php
if (isset($_GET['space'])) {
    $space = shell_exec('du -sh /var/www/html/logs/');
    echo $space;
    exit;
}
?>