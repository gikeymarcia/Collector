<?php
class ReturnVisitController
{                                                           
    private $user;          // participant username         @see __construct()
    private $jsonDir;       // where JSON files are stored  @see __construct()
    private $jsonPath;      // user's JSON filepath         @see isReturning()
    private $oldSession;    // loaded JSON session          @see loadPriorSession()
    private $currentRow;    // row returning to             @see loadPriorSession()
    private $doneLink;      // relative path to done.php    @see __construct()
    private $earlyMsg;      // msg for early birds          @see timeToReturn()
    private $lateMsg;       // msg for too late people      @see timeToReturn()

    public function __construct ()
    {
        global $user;
        $this->user = $user->getUsername();

        global $_PATH;
        $this->jsonDir  = $_PATH->get('JSON Dir');
        $this->doneLink = $_PATH->get('Done');
    }

    public function isReturning()
    {   
        $this->jsonPath = $this->jsonDir . '/' . $this->user . '.json';
        if (FileExists($this->jsonPath) == true) {
            $this->loadPriorSession();
            return true;
        } else {
            return false;
        }
    }
    private function loadPriorSession()
    {
        $handle = fopen($this->jsonPath, 'r');
        $old    = fread($handle, filesize($this->jsonPath));
        $old    = json_decode($old, true);
        $pos    = $old['Position'];

        $this->oldSession = $old;
        $this->currentRow = $old['Trials'][$pos];
        $this->pos = $pos;
    }
    public function alreadyDone() {
        // header("Location: {$this->doneLink}");
        // echo '<meta http-equiv="refresh"; content="5"; url="done.php">';
        // exit;
        $doneCode = 'ExperimentFinished';
        $flag = $this->currentRow['Procedure']['Item'];
        if ($flag == $doneCode) {
            $_SESSION = $this->oldSession;
            $_SESSION['alreadyDone'] = true;
            header("Location: {$this->doneLink}");
            // Need to figure out how to redirect to done
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

        $early; $late;
        if ($min > $sinceFinish) {
            $early = true;
            $dif = $min - $sinceFinish;
            $remaining = $this->durationFormatted($dif);
            $msg = "You are too eary to participate in this part. You can return 
                    in {$remaining} time to start this next part.";
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
    private function durationInSeconds($string)
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
    private function durationFormatted()
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
}