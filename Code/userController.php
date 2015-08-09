<?php
/**
 * Handles the setting, getting, and validating of username given to Collector
 * Also handles setting of user ID
 */
class UserController
{
    private $username;
    private $id;
    private $id_is_set = false;
    private $valid = false;
    private $errorHandler = 'errors';


    /**
     * Allows you to change the default error handler object, $errors, to one of your choosing
     * @param  string $varName will look for variable with the name of the string contents
     * for example, 'mikey' would cause the errors to be reported to `global $mikey`
     */
    public function changeErrorHandler($varName)
    {
        $this->errorHandler = $varName;
    }
    /**
     * Checks $_GET for submitted username, filters characters
     * that shouldn't be in usernames
     */
    public function setUsername()
    {
        $dirtyUsername = filter_input(INPUT_GET, 'Username', FILTER_SANITIZE_EMAIL);
        $illegalCharacters = array('/', '\\', '?', '%', '*', ':', '|', '"', '<', '>' );
        $cleanUsername = str_replace($illegalCharacters, '', $dirtyUsername);
        $this->username = $cleanUsername;
        $this->validateUsername();
    }
    /**
     * Makes sure username is long enough
     * @return [type] [description]
     */
    private function validateUsername ()
    {
        $length = strlen($this->username);
        if ($length < 4) {
            global $$this->errorHandler;
            $msg = 'Login username must longer than 3 characters';
            $$this->errorHandler->add($msg, false);
        } else {
            $this->valid = true;
        }
    }
    /**
     * Does what you'd think it does
     * @return [string/boolean] if the username is valid it is returned,
     * if not then returns false
     */
    public function getUsername()
    {
        if ($this->valid) {
            return $this->username;
        } else {
            return false;
        }
    }
    /**
     * Sets unique ID for each login
     */
    public function setID()
    {
        if (!isset($_SESSION['ID'])) {          // if there is not already an ID set
            if (isset($_GET['ID'])) {           // if the ID is in the URL
                $this->id = $_GET['ID'];        // use it as the ID
            } else {
                $this->id = rand_string();      // otherwise make a new random ID
            }
        } else {
            $this->id = $_SESSION['ID'];        // save existing ID into class
        }
        $this->id_is_set = true;
    }
    /**
     * Does what you'd think
     * @return string gives you the login ID
     */
    public function getID()
    {
        if ($this->id_is_set) {
            return $this->id;
        } else {
            $this->id = 'not_set_' . rand_string();
            return $this->id;
        }
    }
    /**
     * [appendToUsername description]
     * @param  string $suffix what will be added to the username
     */
    public function appendToUsername($suffix)
    {
        $this->username = $this->username . $suffix;
    }
    public function printData()
    {
        echo '<div>This is what we know about the $user' .
        '<ol>';
            echo "<li>{$this->username}</li>";
            echo "<li>{$this->id_is_set}</li>";
            echo "<li>{$this->id}</li>";
            echo "<li>{$this->valid}</li>";
        echo '</ol></div>';
    }
}