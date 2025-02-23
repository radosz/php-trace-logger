<?php
if (isset($_GET['space'])) {
    $space = shell_exec('du -sh /tmp/profiles');
    echo $space;
    exit;
}
?>