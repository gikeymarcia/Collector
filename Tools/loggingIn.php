<?php
    function loginState ($Password) {
        global $_SESSION;
        global $POST;
        global $_GET;
        $hash_algo = 'sha256';
        
        // logged out of session
        if (isset($_GET['restart'])) {
            unset($_SESSION['challenge']);
            $_SESSION['admin'] = FALSE;
        }
        
        $LoginExpiration = 60 * 60 * 2;         // after this many seconds you must login again
        $LoginExpiration = 3;        

        // No password set
        if ($Password == '') {
            return 'setPassword';
        }
        
        // determine how old the NONCE is
        if (isset($_SESSION['challenge'])) {
            $age = time() - $_SESSION['challenge']['birth'];
        } else {
            $age = 0;
        }
        
        // set NONCE if not set, or if too old
        if ((!isset($_SESSION['challenge']))
            OR ($age > (60*60)) ) {
            $_SESSION['challenge'] = array(
                'birth' => time(),
                'NONCE' => makeNonce()
            );
            return 'newVisitor';
        }
        
        if (!isset($_POST['response'])) {
            return 'returning';
        }
        
        // check if the submitted response is correct
        if (isset($POST['response'])) {
            $response = trim($POST['response']);
            $challenge = $_SESSION['challenge']['NONCE'];
            $correctResponse = hash($hash_algo, $challenge.$Password);
            echo $correctResponse . '<br>';
            if ($response == $correctResponse) {
                $_SESSION['admin'] = TRUE;
                $_SESSION['adminBirth'] = time();
                return 'loggedIn';
            }
            else {
                return 'challengeFail';
            }
        } else {
            return 'refresh';
        }
        
        return 'unknownState';
    }
    
    
    #### make a long random string that will be used to salt the password before hashing
    function makeNonce () {
        $toHash = '';                               // make a string
        foreach ($_SERVER as $bit) {                // look at all server variables
            if (mt_rand(1, 4) >= 3) {                   // randomly choose half
                $toHash .= $bit;                            // to add the server variable to the string
            }
            else {
                $toHash .= betterRandString();          // or add a string of random characters to the string
            }
        }
        $toHash .= betterRandString(20);            // always finish string with 20 random characters
        $NONCE = hash('sha512', $toHash, FALSE );   // hash the resulting string (scramble it irreversably)
        
        return $NONCE;                              // return the scrambled output of this process
    }
    
    
    function betterRandString ($stringLength = 7) {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[];,./{}:<>?|';
        $size = strlen($chars);
        $str = '';
        for ($i = 0; $i < $stringLength; $i++) {
            $str .= $chars[mt_rand(0, $size-1)];
        }
        return $str;
    }
?>