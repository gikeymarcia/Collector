<?php
/**
 * This class controls logging into new sessions of existing experiments
 * Logic flow:
 *   1. __construct(): Grab Username and JSON path
 *   
 *   2. isReturning(): Check if JSON file exists for this username
 *         2a. loadPriorSession() if we are returning
 *   
 *   3. alreadyDone():  check if this user is done with all trials
 *   
 *   4. timeToReturn(): check if it is time to return
 *         4a. explainTimeProblem(): if too early/late
 *   
 *   5. reload(): 
 */
class ReturnVisitController
{                                                           
    protected $user;          // participant username         @see __construct()
    protected $jsonDir;       // where JSON files are stored  @see __construct()
    protected $jsonPath;      // user's JSON filepath         @see isReturning()
    protected $oldSession;    // loaded JSON session          @see loadPriorSession()
    protected $currentRow;    // row returning to             @see loadPriorSession()
    protected $doneLink;      // relative path to done.php    @see __construct()
    protected $expLink;       // relative path to experiment.php
    protected $earlyMsg;      // msg for early birds          @see timeToReturn()
    protected $lateMsg;       // msg for too late people      @see timeToReturn()
    protected $done;
    protected $sessionNumber;

    public function __construct($name, $jsonDir, $donePage, $expPage)
    {
        $this->user     = $name;
        $this->jsonDir  = $jsonDir;
        $this->doneLink = $donePage;
        $this->expLink  = $expPage;
    }
    public function isReturning()
    {   
        $this->jsonPath = $this->jsonDir . '/' . $this->user . '.json';
        if (fileExists($this->jsonPath) == true) {
            $this->loadPriorSession();
            return true;
        } else {
            return false;
        }
    }
    protected function loadPriorSession()
    {
        $handle = fopen($this->jsonPath, 'r');
        $old    = fread($handle, filesize($this->jsonPath));
        $old    = json_decode($old, true);
        $pos    = $old['Position'];

        $this->oldSession = $old;
        $this->currentRow = $old['Trials'][$pos-1];
        $this->pos = $pos;
        $this->sessionNumber = $old['Session'];
    }
    public function alreadyDone() {
        $doneCode = 'ExperimentFinished';
        $flag = $this->currentRow['Procedure']['Item'];
        if ($flag == $doneCode) {
            $this->done = true;
            return true;
            // exit;
        } else {
            $this->done = false;
            return false;
        }
    }
    public function reload()
    {
        $_SESSION = $this->oldSession;
        global $_PATH;
        $_PATH = new Pathfinder($_SESSION['Pathfinder']);
        // what to do when reloading to done.php
        if ($this->done === true) {
            $_SESSION['alreadyDone'] = true;
            header("Location: $this->doneLink");
            exit;
        }
        // what to do when reloading to experiment.php
        elseif ($this->done === false) {
            $_SESSION['Start Time']  = date('c');
            header("Location: $this->expLink");
            exit;
        }
    }
    /**
     * Checks if this participate is ready to login to the next session
     * of the experiment. Looks at the 'Min Time' and 'Max Time' cells
     * from the *NewSession* line of the procedure
     * In the case that it is not time to return then it echos the time
     * until return (for min) or a sorry message for when over max
     * @return bool true/false
     */
    public function timeToReturn()
    {
        if (isset($this->currentRow['Procedure']['Min Time'])){
            $min = $this->currentRow['Procedure']['Min Time'];
            $min = $this->durationInSeconds($min);
            if (!is_numeric($min)) { $min = 0; }
        } else {
            $min = 0;
        }
        if (isset($this->currentRow['Procedure']['Max Time'])) {
            $max = $this->currentRow['Procedure']['Max Time'];
            $max = $this->durationInSeconds($max);
            if (!is_numeric($max)) { $max = 0; }
        } else {
            $max = 0;
        }
        $lastFinish = $this->oldSession['LastFinish'];
        $sinceFinish = time() - $lastFinish;

        $early = false; $late = false;
        if ($min > $sinceFinish) {
            $early = true;
            $dif = $min - $sinceFinish;
            $remaining = $this->durationFormatted($dif);
            $msg = "You are too eary to participate in this part. You can return 
                    in $remaining time to start this next part.";
            $this->earlyMsg = $msg;
        }
        if (($sinceFinish > $max)
            AND ($max !== 0)
        ){
            $late = true;
            $msg = "Sorry, you have returned too late to participate.";
            $this->lateMsg = $msg;
        }

        if (($late === true)
            OR ($early === true)
        ){
            return true;
        } else {
            return false;
        }
    }
    /**
     * Shows either the late or early return time error message
     * Each var is initiated with each class instance but the value
     * is only modified if $this->timeToReturn() finds it is too early
     * or too late to come back
     */
    public function explainTimeProblem()
    {
        if ($this->earlyMsg !== null) {
            echo $this->earlyMsg;
        }
        if ($this->lateMsg !== null) {
            echo $this->lateMsg;
        }
    }
    /**
     * Used to turn a formatted sting to a time in seconds
     * @param  string $string excepts things in the format of 3d:2h:5m:10s
     * @return int returns a number
     */
    protected function durationInSeconds($string)
    {
        if ($duration = '') {
            // no duration was given
            return 0;
        }
        // format the duration and convert to array based on colon delimiters
        $durationArray = explode(':', trim(strtolower($duration)));
        $output = 0;
        foreach ($durationArray as $part) {
            // sanitize each part to just the digits
            $value = preg_replace('/[^0-9]/', '', $part);
            if(stripos($part, 'd') !== false) {
                // days in seconds
                $output += ($value * 24 * 60 * 60);
            } else if (stripos($part, 'h') !== false){
                // hours in seconds
                $output += ($value * 60 * 60);
            } else if (stripos($part, 'm') !== false){
                // minutes in seconds
                $output += ($value * 60);
            } else if (stripos($part, 's') !== false){
                // seconds... in seconds
                $output += $value;
            }
        }
        return $output;
    }
    protected function durationFormatted()
    {
        $hours   = floor($durationInSeconds/3600);
        $minutes = floor(($durationInSeconds - $hours*3600)/60);
        $seconds = $durationInSeconds - $hours*3600 - $minutes*60;
        if ($hours > 23) {
            $days = floor($hours/24);
            $hours = $hours - $days*24;
            if ($days < 10) {
                $days = '0' . $days;
            }
        }
        if ($hours < 10) {
            $hours   = '0' . $hours;
        }
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }
        return $days.'d:' . $hours.'h:' . $minutes.'m:' . $seconds.'s';
    }
    public function debug()
    {
        $things = array();
        $things['user'] = $this->user;
        $things['jsonPath'] = $this->jsonPath;
        $things['doneLink'] = $this->doneLink;
        $things['early']    = $this->earlyMsg;
        $things['late']     = $this->lateMsg;

        foreach ($things as $var => $value) {
            echo "<div><b>$var</b><br>$value</div>";
        }
        var_dump('last finish', $this->oldSession['LastFinish']);
        var_dump('CurrentRow',  $this->currentRow);
    }
    public function getSession()
    {
        if (is_numeric($this->sessionNumber)) {
            return $this->sessionNumber;
        } else {
            return 1;
        }
    }
    public function oldCondition()
    {
        $oldConditon  = $this->oldSession['Condition'];
    }
}