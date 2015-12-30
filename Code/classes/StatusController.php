<?php
/**
 * StatusController class.
 */

/**
 *  Writes status begin/end messages.
 *  Logic: upon __construct() grabs browser info
 *  Needs user info $this->updateUser()
 *  Needs condition $this->setConditionInfo()
 *  Needs paths $this->setPaths().
 *
 * Will write a status begin with $this->writeBegin()
 *
 * @todo Write an end status with $this->writeEnd()
 */
class StatusController
{
    /**
     * Path to status begin file.
     *
     * @var string
     */
    protected $beginPath;

    /**
     * Path to status end file.
     *
     * @var string
     */
    protected $endPath;

    /*
     * Browser information
     */

    /**
     * The user's browser.
     *
     * @var string
     */
    protected $browser;

    /**
     * The user's device type.
     *
     * @var string
     */
    protected $deviceType;

    /**
     * The user's operating system.
     *
     * @var type
     */
    protected $OS;

    /*
     * User information
     */

    /**
     * The user's username.
     *
     * @var string
     */
    protected $username;

    /**
     * The user's unique ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The user's output file for the JSON session etc.
     *
     * @var string
     */
    protected $outputFile;

    /**
     * The user's current session number.
     *
     * @var int
     */
    protected $sessionNum;

    /**
     * Information about the current condition.
     *
     * @var array
     */
    protected $condition;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->updateBrowser();
    }

    /**
     * Grabs the browser's user-agent string and returns usable information.
     */
    public function updateBrowser()
    {
        $userAgent = getUserAgentInfo();
        $this->browser = $userAgent->Parent;
        $this->deviceType = $userAgent->Device_Type;
        $this->OS = $userAgent->Platform;
    }

    /**
     * Sets all important user information needed for status writes.
     *
     * @param string $user       User's username.
     * @param string $id         User's unique login ID.
     * @param string $outputFile Path to where the data will be written.
     * @param int    $session    The current session (default: 1).
     */
    public function updateUser($user, $id, $outputFile, $session = 1)
    {
        $this->username = $user;
        $this->id = $id;
        $this->outputFile = $outputFile;
        $this->sessionNum = $session;
    }

    /**
     * Sets all important information about the user's condition.
     *
     * @param array $conditionRow Associative array of condition information
     *                            formatted as a getFromFile() read of the Conditions.csv file.
     */
    public function setConditionInfo($conditionRow)
    {
        $this->condition = $conditionRow;
    }

    /**
     * Relative paths for writing the status begin and end logs.
     *
     * @param string $begin Where to write status begin.
     * @param string $end   Where to write status end.
     */
    public function setPaths($begin, $end)
    {
        $this->beginPath = $begin;
        $this->endPath = $end;
    }

    /**
     * Writes a line to the status begin log.
     */
    public function writeBegin()
    {
        $data = array(
            'Username' => $this->username,
            'ID' => $this->id,
            'Date' => date('c'),
            'Session' => $this->sessionNum,
            'Output_File' => $this->outputFile,
            'Browser' => $this->browser,
            'DeviceType' => $this->deviceType,
            'OS' => $this->OS,
            'IP' => $_SERVER['REMOTE_ADDR'], // some IDEs will say 'filter superglobals!' but: http://stackoverflow.com/a/2018561
        );
        foreach ($this->condition as $key => $value) {
            $data["Cond_$key"] = $value;
        }
        arrayToLine($data, $this->beginPath);
    }

    /**
     * Writes a status end message.
     *
     * @param int $startTime The number of seconds from the UNIX epoch
     * 
     * @todo writeEnd() is copied from done.php --- not functional yet!
     */
    public function writeEnd($startTime)
    {
        $duration = time() - $startTime;
        $durationFormatted = durationFormatted($duration);

        $data = array(
            'Username' => $this->username,
            'ID' => $this->id,
            'Date' => date('c'),
            'Duration' => $duration,
            'Duration_Formatted' => $durationFormatted,
            'Session' => $_SESSION['Session'],
        );
        foreach ($this->condition as $key => $value) {
            $data["Cond_$key"] = $value;
        }
        arrayToLine($data, $this->endPath);
    }
}
