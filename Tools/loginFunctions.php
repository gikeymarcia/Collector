<?php
    function checkPass ($response, $password, $salt, $hash_algo) {
        $correct = hash($hash_algo, $salt . $password);
        if ($correct === $response) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    

    function loginState ($Password) {
        global $_SESSION;
        $LoginExpiration = 60*60*2;         // after this many seconds you must login again
        
        // No password set
        if ($Password === '') {
            return 'noPass';
        }
        
        if (!isset($_SESSION['admin'])) {
            $_SESSION['challenge'] = makeNonce();
            return 'newChallenger';
        }
        
        if ($_SESSION['admin']['status'] === 'failed') {
            $_SESSION['challenge'] = makeNonce();
            return 'wrongPass';
        }
        
        if ($_SESSION['admin']['status'] !== 'loggedIn') {
            $_SESSION['challenge'] = makeNonce();
            return 'newChallenger';
        } else {
            $age = time() - $_SESSION['admin']['birth'];
            if ($age > $LoginExpiration) {
                return 'expired';
            } else {
                return 'loggedIn';
            }
        }
        return 'unknownState';      // how'd you do that?
    }
    
    
    #### show the appropriate response page
    function LoginPrompt ($state) {
        $expired = '<h3>Your session has expired and you must login again to continue</h3>';
        $wrong   = '<p class="wrong">Thank you Mario! But our princess is in another castle... I mean, wrong password</p>';
        $noPass  =
            '<div class="error">' . 
                '<h2>You are not allowed to use <code>Tools</code> until you have set a password</h2>' . 
                '<p> The password can be set within <code>Experiment/settings.php</code></p>' . 
            '</div>';
        $unknown =
            '<p>We have no idea how you got here.' .
               'Post this as an issue on the <a href="http://www.github.com/gikeymarcia/collector">project Github Page</a>.' .
            '</p>';
        $loginPrompt = 
                '<p>Login to access tools</p>' .
                '<input type="password" id="pass"></input>' .
                '<input id="fauxSubmit" type="submit" value="Submit"></input>' .
                '<form id="hashSubmit" action="login.php" method="post">' .
                    '<span id="nonce">' . $_SESSION['challenge'] . '</span>' .
                    '<input id="realInput" name="response" type="text"></input>' .
                '</form>';
        if ($state != 'noPass') {
            $salt = $_SESSION['challenge'];
                '<p>Login to access tools</p>' .
                '<input type="password" id="pass"></input>' .
                '<input id="fauxSubmit" type="submit" value="Submit"></input>' .
                '<form id="hashSubmit" action="login.php" method="post">' .
                    '<span id="nonce">' . $salt . '</span>' .
                    '<input id="realInput" name="response" type="text"></input>' .
                '</form>';
        } else {
            $salt = makeNonce();
        }
            
        echo '<div id="login">';
        switch ($state) {
            case 'noPass':
                echo $noPass;
                break;
            case 'newChallenger':
                echo $loginPrompt;
                break;
            case 'wrongPass':
                echo $wrong . $loginPrompt;
                break;
            case 'expired':
            	echo $expired . $loginPrompt;
            	break;
            default:
                echo $unknown;
                break;
        }
        echo '</div>';
        echo '<div id="salt""><b>salt=</b>' . $salt . '</div>';
    }
    
    
    #### make a long random string that will be used to salt the password before hashing
    function makeNonce ($bits = 512) {
        // adapted from http://stackoverflow.com/a/4145848 User:ircmaxell
        $bytes = ceil($bits / 8);
        $seed = '';
        for ($i = 0; $i < $bytes; $i++) {
            $seed .= chr(mt_rand(0, 255));
        }
        $NONCE = hash('sha512', $seed, FALSE);
        return $NONCE;
    }
?>