<?php
class DebugController
{
    private $debugMode = false;

    /**
     * Figures out if the user logged in as debug
     * @param  [string] $debugName [description]
     * @param  [string] $username  [description]
     */
    public function debugCheck($debugCode, $username)
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
     * changes the data path by appending the /Debug/ sub-folder
     */
    private function modifyPath()
    {
        global $_FILES;
        if ($this->debugMode === true) {
            $_FILES->updateParentPath('data', $_FILES->data->path. '/' . 'Debug/');
        }
    }
    /**
     * How to check if debug mode has been turned on
     * @return boolean `true` if debug mode is on
     */
    public function on()
    {
        if ($this->debugMode === true) {
            return true;
        } else {
            return false;
        }
    } 
}