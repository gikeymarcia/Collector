<?php
    session_start();
    unset($_SESSION['admin']);
    header('Location: ./');     // go back to root of current folder
?>