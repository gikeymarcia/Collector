<?php
function runLogin($password) {
    /* For security reasons, a nonce will be used to hash the password,
       both client and server side. The hashing will take place a number of times,
       to prevent brute-forcing */

    $hashIterations = 10000;

    $loginStatus = checkLoginStatus($password, $hashIterations);

    if ($loginStatus === 'success') {
        $_SESSION['admin']['login'] = time() + 60 * 60 * 2; // stay logged in for 2 hours
        unset($_SESSION['admin']['nonce']);
        header('Location: ' . $_SERVER['REQUEST_URI']);

    } else {
        $_SESSION['admin']['nonce'] = makeNonce();

        createPasswordForm($loginStatus, $hashIterations);
    }

    exit; // either header refresh on success or stop at password form
}
/**
 * Checks that the scrambled+hashed response matches what the server
 * calculates when it does the same scramble+hash with the stored password.
 *
 * This is done so the user doesn't send us the password. Instead we
 * hash(salt+password) server side and challenge the user to get the
 * same resultant hash (i.e., they entered the correct password)
 *
 * By doing this procedure anyone sniffing the transmission is never shown
 * the password and the response they see transmitted cannot be used in the
 * future to login becasue each login is dependent on a unique salt the
 * sever generates for every login attempt
 *
 * @param string $submittedPassword The user response = hash(user input + salt).
 * @param string $nonce             The salt for the password.
 * @param string $hashedPassword    The stored password in Password.php.
 * @param string $hashIterations    The number of times to hash with the nonce
 *
 * @return bool True if the password is correct.
 */
function checkLogin($submittedPassword, $nonce, $hashedPassword, $hashIterations) {
    for ($i=0; $i<$hashIterations; ++$i) {
        $hashedPassword = hash('sha256', $hashedPassword . $nonce);
    }

    return $hashedPassword === $submittedPassword;
}
/**
 * Makes a long random string that will be used to salt the password before
 * hashing it.
 * @param int $bits The number of bits to use.
 * @return string
 */
function makeNonce($bits = 512)
{
    // adapted from http://stackoverflow.com/a/4145848 User:ircmaxell
    $bytes = ceil($bits / 8);
    $seed = '';
    for ($i = 0; $i < $bytes; ++$i) {
        $seed .= chr(mt_rand(0, 255));
    }
    $nonce = hash('sha512', $seed, false);

    return $nonce;
}

// nonce ideas from http://stackoverflow.com/questions/4145531/how-to-create-and-use-nonces
// $_POST['password'] should have been their password hashed with sha256 already,
// then hashed with the nonce and returned
function checkLoginStatus($password, $hashIterations) {
    if (isset($_POST['password'], $_SESSION['admin']['nonce'])) {
        $login = checkLogin($_POST['password'],
                            $_SESSION['admin']['nonce'],
                            $password,
                            $hashIterations);

        // return if verified, give error message if not
        if ($login) {
            return 'success';
        } else {
            return 'Error: Password incorrect';
        }

    } else {
        return '';
    }
}

function createPasswordForm($errorMessage, $hashIterations) {
    $file_sys = new FileSystem();

    $added_scripts = array(
        $file_sys->get_path('Sha256 JS'),
        $file_sys->get_path('root') . '/Admin/Login.js'
    );

    output_page_header($file_sys, 'Collector - Login', $added_scripts);

    ?>
    <div id="PaswordInputArea">
        <div class="error"><?= $errorMessage; ?></div>
        <label>Please enter your password.<br>
            <input  type="password" class="CollectorInput"  id="PasswordInput" required autofocus>
            <button type="button"   class="collectorButton" id="PasswordSubmitButton">Submit</button>
        </label>
    </div>

    <script>
        var hashIterations =  <?= $hashIterations    ?>;
        var nonce          = "<?= $_SESSION['admin']['nonce'] ?>";
    </script>
    <style>
        #flexBody {
            justify-content: flex-start;
        }
        #PaswordInputArea {
            margin: 50px auto;
        }
        .error {
            font-weight: 700;
            color: red;
        }
    </style>
    <?php

    output_page_footer($file_sys);
}
