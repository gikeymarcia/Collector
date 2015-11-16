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
    protected $jsonPath;      // user's JSON filepath         @see isReturning()
    protected $oldSession;    // loaded JSON session          @see loadPriorSession()
    protected $doneLink;      // relative path to done.php    @see __construct()
    protected $expLink;       // relative path to experiment.php
    protected $earlyMsg = null;      // msg for early birds          @see timeToReturn()
    protected $lateMsg  = null;       // msg for too late people      @see timeToReturn()
    protected $done;
    protected $sessionNumber;

    public function __construct($jsonPath, $donePage, $expPage)
    {
        $this->jsonPath = $jsonPath;
        $this->doneLink = $donePage;
        $this->expLink  = $expPage;
    }
    public function isReturning()
    {   
        $path = $this->jsonPath;
        if (fileExists($path) == true) {
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

        $this->oldSession = $old;
        $this->sessionNumber = $old['Session'];
    }
    public function alreadyDone() {
// echo "whyyyy: " . $this->oldSession['state'] . "<br>";
        if ($this->oldSession['state'] == 'done') {
// var_dump($this->oldSession,'old session');
            $this->done = true;
            return true;
        } else {
// echo "done is false<br>";
            $this->done = false;
            return false;
        }
    }
    public function reloadToExperiment(Pathfinder $pathfinder, user $user)
    {
        $_SESSION = $this->oldSession;
        $user->setSession($this->getSession());
        $user->feedPathfinder($pathfinder);
        $status = unserialize($_SESSION['Status']);
        $status->updateUser(
            $user->getUsername(),
            $user->getID(),
            $user->getOutputFile(),
            $user->getSession()
        );
        $status->writeBegin();
        $_SESSION['Status'] = serialize($status);
        $_SESSION['Start Time']  = time();
        $_SESSION['state']       = 'exp';
        header("Location: $this->expLink");
        exit;
    }
    public function reloadToDone()
    {
        $_SESSION = $this->oldSession;
        header("Location: $this->doneLink");
        exit;
    }
    /**
     * Checks if this participate is ready to login to the next session
     * of the experiment. Return time is based on teh 'Min Time' and 'Max Time' cells
     * from the `NewSession` line of the procedure
     * In the case that it is not time to return then it echos the time
     * until return (for min) or a sorry message for when over max
     * @return bool true/false
     */
    public function timeToReturn()
    {
        $now = time();
        $minRet = $this->oldSession['Min Return'];
        $maxRet = $this->oldSession['Max Return'];

        // determines if it is too late to return
        $early = false; $late = false;

        if ($maxRet !== false AND $now > $maxRet) {
            $late = true;
            $msg = "Sorry, you have returned too late to participate.";
            $this->lateMsg = $msg;
        }
        if ($now < $minRet) {
            $early = true;
            $dif   = $minRet - $now;
            $remaining = durationFormatted($dif);
            $msg = "You are too eary to participate in this part. You can return 
                    in $remaining to start this next part.";
            $this->earlyMsg = $msg;
        }
// echo "now: $now<br>
//      min_return: $minRet<br>
//      max_return: $maxRet<br>
//      dif: $dif<br>
//      late: $late<br>
//      early:$early<br>";
        // return whether or not to continue on
        if (($late == true)
            OR ($early == true)
        ){
            return false;
        } else {
            return true;
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
// echo "this is from explainTimeProblem and earlyMsg is : '$this->earlyMsg'<br> latemsg is: $this->lateMsg";
        if ($this->earlyMsg !== null) {
            echo $this->earlyMsg;
        }
        if ($this->lateMsg !== null) {
            echo $this->lateMsg;
        }
        exit;
    }
    public function debug()
    {
        $things = array();
        $things['jsonPath'] = $this->jsonPath;
        $things['doneLink'] = $this->doneLink;
        $things['early']    = $this->earlyMsg;
        $things['late']     = $this->lateMsg;

        foreach ($things as $var => $value) {
            echo "<div><b>$var</b><br>$value</div>";
        }
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
        return $this->oldSession['Condition'];
    }
}