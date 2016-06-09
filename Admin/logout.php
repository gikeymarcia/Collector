<?php
    /* We need to start the session, but there might be some
       classes saved in there, so we have to go through
       initiateCollector in order to get the autoloader. */
    require '../Code/initiateCollector.php';
    unset($_SESSION['admin']);
    header('Location: ..');
    exit;
