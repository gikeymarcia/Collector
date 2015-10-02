<?php
class DebugController
{
    protected $debugMode = false;

    /**
     * Figures out if the user logged in as debug
     * @param  [string] $debugName [description]
     * @param  [string] $username  [description]
     */
    public function debugCheck($username, $debugCode)
    {
        if ((strlen($debugCode) > 0)
            AND (substr($username, 0, strlen($debugCode) === $debugCode))
        ) {
            $this->debugMode = true;
            $this->modifyPath();
            $_SESSION['Debug'] = true;
        } else {
            $_SESSION['Debug'] = false;
        }
    }
    /**
     * How to check if debug mode has been turned on
     * @return boolean `true` if debug mode is on
     */
    public function is_on()
    {
        if ($this->debugMode === true) {
            return true;
        } else {
            return false;
        }
    } 
}