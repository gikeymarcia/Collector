<?php
/**
 * ErrorController class.
 */

/**
 * @todo update all docblocks in ErrorController
 * 
 * Used to keep track of error messages during the login process
 */
class ErrorController
{
    /**
     * All of the error messages.
     * @var array
     */
    protected $details = array();
    
    /**
     * Indicates whether errors can stop the entire experiment.
     * @var bool
     */
    protected $allowShowStopper = true;

    /**
     * logs the given error message.
     * @param string  $errMsg      [details of specific error]
     * @param boolean $showStopper [if set to `true` immediately print error and stop program]
     */
    public function add($errMsg, $showStopper = false)
    {
        if (strlen($errMsg) > 0) {
            $this->count++;
            $this->details[] = $errMsg;
        }
        if (($showStopper == true)
            AND ($this->allowShowStopper == true)
        ){
            echo "$errMsg<br>";
            $this->printErrors();
            exit;
        }
    }
    /**
     * Show all errors.
     */
    public function printErrors()
    {
        if (count($this->details) > 0) {
            echo
            "<style type='text/css' media='screen'>
                .err {
                    margin-left: .8em;
                    margin-top:  1em;
                }
            </style>";
            echo '<ol class="err">';
            foreach ($this->details as $pos => $messsage) {
                $li = "<li>$messsage</li>";
                echo $li;
            }
            echo '</ol>';
        }
    }
    /**
     * Get the number of errors found.
     * @return int Number of errors stored in this object.
     */
    public function count()
    {
        return count($this->details);
    }
    /**
     * trasnforms the error handler so it doesn't exit
     * the code when a $showstopper error occurs
     */
    public function noShowStoppers()
    {
        $this->allowShowStopper = false;
    }
    
    /**
     * Indicates whether any errors are present.
     * @return boolean True if errors are present.
     */
    public function arePresent()
    {
        if ($this->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Converts all logged errors to a single string and returns the string.
     * @return string All of the logged errors.
     * @see ErrorController::printErrors.
     */
    public function __toString()
    {
        ob_start();
        $this->printErrors();
        $string = ob_get_clean();
        return $string;
    }
}