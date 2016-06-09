<?php
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
