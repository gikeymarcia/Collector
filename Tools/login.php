<?php
    require '../Code/fileLocations.php';
    require $up . $expFiles . 'Settings.php';
    require 'loginFunctions.php';
    session_start();
    $hash_algo = 'sha256';
    $nonce = $_SESSION['admin']['challenge'];
    
    if(isset($_POST['response'])) {
        $response = $_POST['response'];
        if (checkPass($response, $config->password, $nonce, $hash_algo) === true) {
            $_SESSION['admin']['challenge'] = makeNonce();
            $_SESSION['admin']['status'] = 'loggedIn';
            $_SESSION['admin']['birth'] = time();
        } else {
            $_SESSION['admin']['status'] = 'failed';
            $_SESSION['admin']['birth'] = time();
        }
    }
    
    header('Location: ./');     // go back to root of current folder
?>
