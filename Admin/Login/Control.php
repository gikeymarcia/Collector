<?php
/* The intent of this page is to ask users for a password, and when submitted,
   check it against the password we hold on the server. If they match,
   return "success" to the script that included this file. */
/* For security reasons, a nonce will be used to hash the password,
   both client and server side. The hashing will take place a number of times,
   to prevent brute-forcing */

$hashIterations = 10000;

require __DIR__ . '/LoginFunctions.php';

$loginResult = require __DIR__ . '/checkLogin.php';

if ($loginResult === 'success') {
    $_SESSION['admin']['login'] = time() + 60 * 60 * 2; // stay logged in for 2 hours
    return;
}

$_SESSION['nonce'] = makeNonce();

require __DIR__ . '/PasswordForm.php';
exit; // prevent further script execution, no tools may be run until logged in
