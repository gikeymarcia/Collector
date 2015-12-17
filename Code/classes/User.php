<?php
/**
 * Handles the setting, getting, and validating of username given to Collector
 * Also handles setting of user ID
 */
class User
{
    protected $username;
    protected $id;
    protected $sessionNumber = 1;
    protected $valid = null;
    protected $errObj;

    public function __construct($name, ErrorController $errorController)
    {
        $this->errObj = $errorController;
        $this->setUsername($name);
    }
    /**
     * Takes a string and sets it as the username
     * Chains:
     *     username filtering
     *     username validation
     *     ID creation
     * @param string $name username for participant
     */
    public function setUsername($name)
    {
        $name = filter_var($name, FILTER_SANITIZE_EMAIL);
        $illegalCharacters = array('/', '\\', '?', '%', '*', ':', '|', '"', '<', '>' );
        $cleanUsername = str_replace($illegalCharacters, '', $name);
        $this->username = $cleanUsername;
        $this->validateUsername();
        $this->setID();
    }
    /**
     * Makes sure username is long enough
     * @return [type] [description]
     */
    protected function validateUsername ()
    {
        $this->checkNameLength();
    }
    protected function checkNameLength()
    {
        $length = strlen($this->username);
        if ($length < 4) {
            $msg = 'Login username must longer than 3 characters';
            $this->errObj->add($msg, false);
        } else {
            if ($this->valid !== false) {
                $this->valid = true;
            }
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
    protected function setID()
    {
        $idLength = 10;
        if (!isset($_SESSION['ID'])) {          // if there is not already an ID set
            if (isset($_GET['ID'])) {           // if the ID is in the URL
                $this->id = $_GET['ID'];        // use it as the ID
            } else {
                $this->id = rand_string($idLength); // otherwise make a new random ID
            }
        } else {
            $this->id = $_SESSION['ID'];        // save existing ID into class
        }
    }
    /**
     * Does what you'd think
     * @return string gives you the login ID
     */
    public function getID()
    {
        if (strlen($this->id) > 0) {
            return $this->id;
        } else {
            return false;
        }
    }
    public function setSession($number)
    {
        if (is_int($number) AND ($number > 0)) {
            $this->sessionNumber = $number;
        }
    }
    /**
     * [appendToUsername description]
     * @param  string $suffix what will be added to the username
     */
    public function appendToUsername($suffix)
    {
        $potential = $this->username . $suffix;
        $this->setUsername($potential);
    }
    public function getSession()
    {
        return $this->sessionNumber;
    }
    /**
     * [getOutputFile description]
     * @return [type] [description]
     */
    public function getOutputFile()
    {
        $name = 'Output_Session' . 
                $this->sessionNumber . '_' . 
                $this->username . '_' . 
                $this->id .
                '.csv';
        return $name;
    }
    /**
     * Allows you to change the default error handler object, $errors, to one of your choosing
     * @param  string $varName will look for variable with the name of the string contents
     * for example, 'mikey' would cause the errors to be reported to `global $mikey`
     */
    public function changeErrorHandler(ErrorController $altErrObj)
    {
        $this->errObj = $altErrObj;
    }
    public function printData()
    {
        echo
        "<div>This is what we know about the \$user
             <ol>
                <li><b>Name:</b> $this->username</li>
                <li><b>Id#:</b> $this->id</li>
                <li><b>is Valid:</b> $this->valid</li>
                <li><b>Session#:</b> $this->sessionNumber</li>
             </ol>
         </div>";
    }
    public function feedPathfinder (Pathfinder $pathfinder) 
    {
        $pathfinder->setDefault('Username', $this->getUsername());
        $pathfinder->setDefault('Output',   $this->getOutputFile());
    }
}