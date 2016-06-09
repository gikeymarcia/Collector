<?php
// use a separate session for admin, so that it doesnt get mixed up with experiment data
$sessDir = __DIR__ . '/sess';
if (!is_dir($sessDir)) mkdir($sessDir, 0777, true);
session_save_path($sessDir);
unset($sessDir);

require __DIR__ . '/../Code/initiateCollector.php';

// check if login has expired
if (isset($_SESSION['admin']['login'])) {
    if (time() > $_SESSION['admin']['login']) {
        unset($_SESSION['admin']['login']);
    }
}

// check if we have logged in
if (!isset($_SESSION['admin']['login'])) {
    // haven't logged in, run password script
    require __DIR__ . '/Login/Control.php';
}
