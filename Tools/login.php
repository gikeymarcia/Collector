<?php
require "../Code/classes/Settings.php";
require '../Code/initiateCollector.php';
require 'loginFunctions.php';

$hash_algo = 'sha256';
// $nonce = (isset($_SESSION['admin']['challenge'])) ? 
//     $_SESSION['admin']['challenge'] : "";

$nonce = $_SESSION['admin']['challenge'];
   

$response = filter_input(INPUT_POST, 'response', FILTER_SANITIZE_STRING);
if ($response !== null) {
    if (checkPass($response, $_SETTINGS->password, $nonce, $hash_algo) === true) {
        // $_SESSION['admin']['challenge'] = makeNonce();
        $_SESSION['admin']['status'] = 'loggedIn';
        $_SESSION['admin']['birth'] = time();
    } else {
        $_SESSION['admin']['status'] = 'failed';
        $_SESSION['admin']['birth'] = time();
    }
}

echo "response: ".$response."<br>";
echo "password: ".$_SETTINGS->password."<br>";
echo "nonce: ".$nonce."<br>";
echo "challenge: ".$_SESSION['admin']['challenge']."<br>";
?>
<pre>
    <?php var_dump($_SESSION); ?>
</pre>

<?php 
// go back to root of current folder
// header('Location: ./');