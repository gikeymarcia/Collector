<?php
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
    $hashedPassword = hash('sha256', $hashedPassword); // password should have been saved as sha256
    
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
