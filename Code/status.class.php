<?php

$status->writeBegin();
/**
*  
*/
class status
{
    // browser info
    private $browser;
    private $deviceType;
    private $OS;
    // user info
    private $username;
    private $id;
    private $outputFile;
    
    function __construct()
    {
        $this->updateBrowser();
        $this->updateUser();                // needs $user
        
    }
    public function updateBrowser()
    {
        $userAgent = getUserAgentInfo();
        $this->browser    = $userAgent->Parent;
        $this->deviceType = $userAgent->Device_Type;
        $this->OS         = $userAgent->Platform;
    }
    public function updateUser($user, $id, $session=1)
    {
        // global $user;
        $this->username   = $userObj->getUsername();
        $this->id         = $userObj->getID();
        $this->outputFile = $userObj->getOutputFile();
    }
    public function writeBegin()
    {
        $UserData = array(
        'Username'              => $this->username,
        'ID'                    => $this->id,
        'Date'                  => $_SESSION['Start Time'],
        'Session'               => $_SESSION['Session'] ,
        'Condition_Number'      => $_SESSION['Condition']['Number'],
        'Condition_Description' => $_SESSION['Condition']['Condition Description'],
        'Output_File'           => $this->outputFile,
        'Stimuli_File'          => $_SESSION['Condition']['Stimuli'],
        'Procedure_File'        => $_SESSION['Condition']['Procedure'],
        'Browser'               => $this->browser,
        'DeviceType'            => $this->deviceType,
        'OS'                    => $this->OS,
        'IP'                    => $_SERVER["REMOTE_ADDR"],
    }
}
?>