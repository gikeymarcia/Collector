<?php
/**
*  Writes status begin/end messages
*  -- currently only used in login.php but will be expanded to write end status later
*/
class status
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
    protected $number;
    protected $description;
    protected $stimuli;
    protected $procedure;
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
        $this->number      = $conditionRow['Number'];
        $this->description = $conditionRow['Condition Description'];
        $this->stimuli     = $conditionRow['Stimuli'];
        $this->procedure   = $conditionRow['Procedure'];
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
            'Username'              => $this->username,
            'ID'                    => $this->id,
            'Date'                  => date('c'),
            'Session'               => $this->sessionNum,
            'Condition_Number'      => $this->number,
            'Condition_Description' => $this->description,
            'Output_File'           => $this->outputFile,
            'Stimuli_File'          => $this->stimuli,
            'Procedure_File'        => $this->procedure,
            'Browser'               => $this->browser,
            'DeviceType'            => $this->deviceType,
            'OS'                    => $this->OS,
            'IP'                    => $_SERVER["REMOTE_ADDR"]
        );
        arrayToLine($UserData, $this->beginPath);
    }
}
?>