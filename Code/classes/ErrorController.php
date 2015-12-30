<?php
/**
 * ErrorController class.
 */

/**
 * Used to keep track of error messages during the login process.
 */
class ErrorController
{
    /**
     * All of the error messages.
     *
     * @var array
     */
    protected $details = array();

    /**
     * Indicates whether errors can stop the entire experiment.
     *
     * @var bool
     */
    protected $allowShowStopper = true;

    /**
     * Logs the given error message.
     *
     * @param string $errMsg      Details of specific error.
     * @param bool   $showStopper Set to 'true' to immediately print the error
     *                            and stop the program.
     * 
     * @todo should showstopper actually push to an error page that dumps all errors?
     */
    public function add($errMsg, $showStopper = false)
    {
        if (strlen($errMsg) > 0) {
            $this->details[] = $errMsg;
        }
        if (($showStopper == true) && ($this->allowShowStopper == true)) {
            echo "$errMsg<br>";
            $this->printErrors();
            exit;
        }
    }

    /**
     * Prints all errors to the page.
     * 
     * @todo if we change errors to go to an error page, styles should be in a css file for that page
     */
    public function printErrors()
    {
        if (count($this->details) > 0) {
            echo "<style type='text/css' media='screen'>
                .err { margin-left: .8em; margin-top:  1em; }
            </style>";
            echo '<ol class="err">';
            foreach ($this->details as $messsage) {
                $li = "<li>$messsage</li>";
                echo $li;
            }
            echo '</ol>';
        }
    }

    /**
     * Get the number of errors logged.
     *
     * @return int Number of errors stored in this object.
     */
    public function count()
    {
        return count($this->details);
    }

    /**
     * Prevents show stoppers from occuring.
     * Sets ErrorController::allowShowStopper to false.
     */
    public function noShowStoppers()
    {
        $this->allowShowStopper = false;
    }

    /**
     * Indicates whether any errors are present.
     *
     * @return bool True if errors are present.
     */
    public function arePresent()
    {
        return ($this->count() > 0) ? true : false;
    }

    /**
     * Converts all logged errors to a single string and returns the string.
     *
     * @return string All of the logged errors.
     *
     * @uses ErrorController::printErrors() Gets error dump using this method.
     */
    public function __toString()
    {
        ob_start();
        $this->printErrors();
        $string = ob_get_clean();

        return $string;
    }
}
