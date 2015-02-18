<?php
    require '../Code/fileLocations.php';
    require $up . $expFiles . 'Settings.php';
    require 'loginFunctions.php';
    session_start();
    $hash_algo = 'sha256';
    $nonce = $_SESSION['challenge'];
    
    if(isset($_POST['response'])) {
        $response = $_POST['response'];
        if (checkPass($response, $Password, $nonce, $hash_algo) === TRUE) {
            $_SESSION['challenge'] = makeNonce();
            $_SESSION['admin'] = array(
                'status' => 'loggedIn',
                'birth'  => time()
            );
        } else {
            $_SESSION['admin'] = array(
                'status' => 'failed',
                'birth'  => time()
            );
        }
    }
    
    header('Location: ./');     // go back to root of current folder
?>