<?php
/**
 * User class.
 */

/**
 * Handles the setting, getting, and validating of username given to Collector.
 * Also handles setting of user ID.
 */
class User
{
    /**
     * The username of the participant.
     *
     * @var string
     */
    protected $username;

    /**
     * Unique ID for the participant.
     *
     * @var type
     */
    protected $id;

    /**
     * The current session for this participant.
     *
     * @var type
     */
    protected $sessionNumber = 1;

    /**
     * Indicates whether the username is valid or not.
     *
     * @var bool
     */
    protected $valid = null;

    /**
     * ErrorController object for handling errors.
     *
     * @var ErrorController
     */
    protected $errObj;

    /**
     * Constructor.
     *
     * @param string          $name            The participant's username.
     * @param ErrorController $errorController Object for handling errors.
     */
    public function __construct($name, ErrorController $errorController)
    {
        $this->errObj = $errorController;
        $this->setUsername($name);
    }

    /**
     * Takes a string and sets it as the username.
     * Desired username is filtered, set as the username, validated, and then
     * an ID is created and set for it.
     *
     * @param string $input The participant's desired username.
     *
     * @uses User::username
     * @uses User::validateUsername()
     * @uses User::setID()
     */
    public function setUsername($input)
    {
        $username = filter_var($input, FILTER_SANITIZE_EMAIL);
        $illegalCharacters = array('/', '\\', '?', '%', '*', ':', '|', '"', '<', '>');
        $cleanUsername = str_replace($illegalCharacters, '', $username);
        $this->username = $cleanUsername;
        $this->validateUsername();
        $this->setID();
    }

    /**
     * Checks if username is valid and then sets User::valid to true or false.
     * 
     * @todo move code setting User::valid to User::validateUsername()?
     */
    protected function validateUsername()
    {
        $this->checkNameLength();
    }

    /**
     * Checks that the username is longer than three characters.
     *
     * @uses User::valid Sets the valid state depending on if the check passes.
     */
    protected function checkNameLength()
    {
        $length = strlen($this->username);
        if ($length < 4) {
            $msg = 'Login username must longer than 3 characters';
            $this->errObj->add($msg, false);
        } elseif ($this->valid !== false) {
            $this->valid = true;
        }
    }

    /**
     * Returns the username if it is valid.
     *
     * @return string|bool Filtered username or false if it is invalid.
     *
     * @uses User::username Returns this if it is valid.
     */
    public function getUsername()
    {
        return $this->valid ? $this->username : false;
    }

    /**
     * Sets unique ID for each login.
     *
     * @uses User::id Sets this value after finding or creating it.
     */
    protected function setID()
    {
        $idLength = 10;

        if (!isset($_SESSION['ID'])) {
            // an ID is not yet set
            $getId = filter_input(INPUT_GET, 'ID', FILTER_SANITIZE_STRING);
            $this->id = ($getId !== null) ? $getId : rand_string($idLength);
        } else {
            // ID is already set, store it here
            $this->id = $_SESSION['ID'];
        }
    }

    /**
     * Returns the participant's ID.
     *
     * @return string The participant's unique ID.
     *
     * @uses User::id Returned by this function if it exists.
     */
    public function getID()
    {
        return (strlen($this->id) > 0) ? $this->id : false;
    }

    /**
     * Sets the current session number (for use in multi-session experiments).
     *
     * @param int $number The number of the session.
     *
     * @uses User::sessionNumber Sets a new value to this if the value is valid.
     */
    public function setSession($number)
    {
        if (is_int($number) && ($number > 0)) {
            $this->sessionNumber = $number;
        }
    }

    /**
     * Appends a string to the username and passes it to User::setUsername.
     *
     * @param string $suffix The string to append to User::username.
     *
     * @uses User::username Appends suffix to current username.
     * @uses User::setUsername() Passes the new value for filtering/validation.
     */
    public function appendToUsername($suffix)
    {
        $potential = $this->username.$suffix;
        $this->setUsername($potential);
    }

    /**
     * Gets the current session number.
     *
     * @return int The currently stored session number.
     *
     * @uses User:sessionNumber Returns this value.
     */
    public function getSession()
    {
        return $this->sessionNumber;
    }

    /**
     * Creates and returns the output file's name.
     *
     * @return string The name of the output file.
     *
     * @uses User::sessionNumber Stitches into the output file name.
     * @uses User::username Stitches into the output file name.
     * @uses User::id Stitches into the output file name.
     */
    public function getOutputFile()
    {
        return "Output_Session{$this->sessionNumber}_{$this->username}_{$this->id}.csv";
    }

    /**
     * Changes the default error handler to a new instance of ErrorController.
     *
     * @param ErrorController $altErrObj Error handler object.
     *
     * @uses User::errObj Sets this value to the new error handler.
     */
    public function changeErrorHandler(ErrorController $altErrObj)
    {
        $this->errObj = $altErrObj;
    }

    /**
     * Echoes an HTML formatted list of the User's information.
     *
     * @uses User::username
     * @uses User::id
     * @uses User::valid
     * @uses User::sessionNumber
     * 
     * @todo convert to return a string instead of echoing?
     */
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

    /**
     * Sets relevant User information in the passed Pathfinder object.
     *
     * @param Pathfinder $pathfinder The Pathfinder to update.
     */
    public function feedPathfinder(Pathfinder $pathfinder)
    {
        $pathfinder->setDefault('Username', $this->getUsername());
        $pathfinder->setDefault('Output',   $this->getOutputFile());
    }
}
