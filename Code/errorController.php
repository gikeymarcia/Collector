<?php
/**
 * Used to keep track of error messages during the login process
 */
class ErrorController
{
    private $count;
    private $details = array();

    /**
     * logs the given error message
     * @param string  $errMsg      [details of specific error]
     * @param boolean $showStopper [if set to `true` immediately print error and stop program]
     */
    public function add($errMsg, $showStopper = false)
    {
        if (strlen($errMsg) > 0) {
            $this->count++;
            $this->details[] = $errMsg;
        }
        if ($showStopper == true) {
            echo $errMsg;
            exit;
        }
    }
    /**
     * Show all errors
     */
    public function printErrors()
    {
        if (count($this->details) > 0) {
            echo '<ol>';
            foreach ($this->details as $pos => $messsage) {
                $li = '<li>' . $messsage . '</li>';
                echo $li;
            }
            echo '</ol>';
        }
    }
}
?>