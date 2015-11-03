<?php
class debugController
{
    protected $debugMode = false;   // debugCheck() can change this to true
    protected $username;
    protected $debugName;
    protected $debugSwitch;

    /**
     * Save values needed to check debug and runs the debugCheck()
     * @param string  $username  user's login name
     * @param string  $debugName codeword to login with/as to start debug mode
     * @param boolean $setting   value from config that can turn debug on/off for all logins
     */
    public function __construct($username, $debugName, $setting)
    {
        $this->username    = $username;
        $this->debugName   = $debugName;
        $this->debugSwitch = $setting;
        $this->debugCheck();
    }
    /**
     * Method that runs the checks.  If any check returns true then debug mode is turned on.
     * A boolean is saved to $_SESSION['Debug'] which tells if debug is on/off
     * @return [type]           [description]
     */
    public function debugCheck()
    {
        if ($this->checkName() OR $this->checkConfig()){
            $this->debugMode = true;
        } else {
            $this->debugMode = false;
        }
        $_SESSION['Debug'] = $this->debugMode;
    }
    /**
     * Checks if the username begins with the debug name
     * @return boolean true/false login name begins with debug code
     */
    protected function checkName()
    {
        $name = $this->username;
        $code = $this->debugName;
        $dbLen = strlen($code);
        if (($dbLen > 0)
            AND (substr($name, 0, $dbLen) === $code)
        ) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Checks if the configuration file has specified debug mode be turned on
     * @return boolean
     */
    protected function checkConfig()
    {
        if ($this->debugSwitch == true) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * public way to check if debug mode is on/off
     * @return boolean true=debug is on, false=debug is off
     */
    public function is_on()
    {
        if ($this->debugMode === true) {
            return true;
        } else {
            return false;
        }
    } 
    public function changeName($name)
    {
        $this->username = $name;
        $this->debugCheck();
    }
}