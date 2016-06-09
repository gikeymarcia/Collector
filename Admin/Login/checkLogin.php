<?php
// nonce ideas from http://stackoverflow.com/questions/4145531/how-to-create-and-use-nonces
// $_POST['password'] should have been their password hashed with sha256 already,
// then hashed with the nonce and returned
if (isset($_POST['password'], $_SESSION['nonce'])) {
    $login = checkLogin($_POST['password'],
                        $_SESSION['nonce'],
                        $_SETTINGS->password,
                        $hashIterations);

    // return if verified, give error message if not
    if ($login) {
        return 'success';
    } else {
        return '<div class="error">Error: Password incorrect</div>';
    }
} else {
    return '';
}
