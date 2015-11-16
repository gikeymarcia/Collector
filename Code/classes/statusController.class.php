<?php
/**
*  Writes status begin/end messages
*  -- currently only used in login.php but will be expanded to write end status later
*  Logic: upon __construct() grabs browser info
*  Needs user info $this->updateUser()
*  Needs condition $this->setConditionInfo()
*  Needs paths $this->setPaths()
*
* Will write a status begin with $this->writeBegin()
* FUTURE: will write an end with $this->writeEnd()
*  
*/
class statusController
{
    // browser info
    protected $browser;
    protected $deviceType;
    protected $OS;
    // user info
    protected $username;
    protected $id;
    protected $outputFile;
    protected $sessionNum;
    // Conditon Info
    protected $condition;
    // protected $number;
    // protected $description;
    // protected $stimuli;
    // protected $procedure;
    // Where to write status begin/end
    protected $beginPath;
    protected $endPath;

    function __construct()
    {
        $this->updateBrowser();
    }
    /**
     * Grabs the browser's useragent string and returns usable information
     */
    public function updateBrowser()
    {
        $userAgent = getUserAgentInfo();
        $this->browser    = $userAgent->Parent;
        $this->deviceType = $userAgent->Device_Type;
        $this->OS         = $userAgent->Platform;
    }
    /**
     * sets all important user information needed for status writes
     * @param  string  $user       User's name
     * @param  string  $id         User's login ID
     * @param  string  $outputFile filename where the data will be written
     * @param  integer $session    Which session is this user logging into? Defaults to 1
     */
    public function updateUser($user, $id, $outputFile, $session=1)
    {
        $this->username   = $user;
        $this->id         = $id;
        $this->outputFile = $outputFile;
        $this->sessionNum = $session;
    }
    /**
     * sets all important information about the user's condition
     * @param array $conditionRow   Keyed array from a getFromFile() read of Conditions.csv
     */
    public function setConditionInfo($conditionRow)
    {
        $this->condition = $conditionRow;
    }
    /**
     * relative paths' for writing the status begin and end logs
     * @param string $begin Where to write status begins
     * @param string $end   Where to write status ends
     */
    public function setPaths($begin, $end)
    {
        $this->beginPath = $begin;
        $this->endPath   = $end;
    }
    /**
     * Writings a line to the status begin log
     */
    public function writeBegin()
    {
        $UserData = array(
            'Username'    => $this->username,
            'ID'          => $this->id,
            'Date'        => date('c'),
            'Session'     => $this->sessionNum,
            'Output_File' => $this->outputFile,
            'Browser'     => $this->browser,
            'DeviceType'  => $this->deviceType,
            'OS'          => $this->OS,
            'IP'          => $_SERVER["REMOTE_ADDR"]
        );
        foreach ($this->condition as $key => $value) {
            $UserData["Cond_$key"] = $value;
        }
        arrayToLine($UserData, $this->beginPath);
    }
    /**
     * Write a status end message
     * @param  int $startTime the # of seconds from the UNIX epoch
     * @return n/a            Writes a file but doens't return text
     */
    public function writeEnd($startTime)
    {
        $duration = time() - $startTime;
        $durationFormatted = durationFormatted($duration);
        // below is copied from done.php
        // Not functional yet so the metho is protected to prevent it from being run from within the experiment
        $data = array(
            'Username'           => $this->username,
            'ID'                 => $this->id,
            'Date'               => date('c'),
            'Duration'           => $duration,
            'Duration_Formatted' => $durationFormatted,
            'Session'            => $_SESSION['Session'],
        );
        foreach ($this->condition as $key => $value) {
            $UserData["Cond_$key"] = $value;
        }
        arrayToLine($data, $this->endPath);
    }
}
?>