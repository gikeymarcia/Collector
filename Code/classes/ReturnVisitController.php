<?php
/**
 * ReturnVisitController class.
 */

namespace Collector;

/**
 * Controls logging into new sessions of existing experiments.
 *
 * Logic flow:
 * <ol>
 *   <li>__construct(): Grab Username and JSON path</li>
 *   <li>isReturning(): Check if JSON file exists for this username</li>
 *   <ol type="a"><li>loadPriorSession(): if we are returning</li></ol>
 *   <li>alreadyDone(): Check if this user is done with all trials</li>
 *   <li>timeToReturn(): Check if it is time to return</li>
 *   <ol type="a"><li>explainTimeProblem(): if too early/late</li></ol>
 *   <li>reload()</li>
 * </ol>
 */
class ReturnVisitController
{
    /**
     * User's JSON filepath.
     *
     * @var string
     */
    protected $sessionStoragePath;

    /**
     * The loaded JSON session.
     *
     * @var array
     */
    protected $oldSession;

    /**
     * Relative path to done.php.
     *
     * @var string
     */
    protected $doneLink;

    /**
     * Relative path to experiment.php.
     *
     * @var string
     */
    protected $expLink;

    /**
     * Message to give users that return too early.
     *
     * @var string
     */
    protected $earlyMsg = null;

    /**
     * Message to give users that return too late.
     *
     * @var type
     */
    protected $lateMsg = null;

    /**
     * Indicates whether the previous session is complete or not.
     *
     * @var bool
     */
    protected $done = false;

    /**
     * Holds the number of the session.
     *
     * @var int
     */
    protected $sessionNumber;

    /**
     * Constructor.
     *
     * @param string $sessionStoragePath The location of the previously stored
     *                                   $_SESSION data.
     * @param string $doneLink           The relative path to done.php
     * @param string $expLink            The relative path to Experiment.php
     */
    public function __construct($sessionStoragePath, $doneLink, $expLink)
    {
        $this->sessionStoragePath = $sessionStoragePath;
        $this->doneLink = $doneLink;
        $this->expLink = $expLink;
    }

    /**
     * Determines whether the current participant is a returning user.
     *
     * @return bool True if returning and prior session exists, else false.
     *
     * @uses ReturnVisitController::jsonPath Uses this path to determine if a
     *          prior session occurred.
     */
    public function isReturning()
    {
        if (fileExists($this->sessionStoragePath) !== false) {
            $this->loadPriorSession();

            return true;
        }

        return false;
    }

    /**
     * Loads the session information from the user's last visit.
     *
     * @uses ReturnVisitController::oldSession Sets the old session here.
     * @uses ReturnVisitController::sessionNumber Sets the old session # here.
     * @uses ReturnVisitController::jsonPath Reads the old session from here.
     */
    protected function loadPriorSession()
    {
        $handle = fopen($this->sessionStoragePath, 'r');
        $data = fread($handle, filesize($this->sessionStoragePath));
        $old = unserialize(base64_decode($data));

        $this->oldSession = $old;
        $this->sessionNumber = $old['Session'];
    }

    /**
     * Determines if the oldSession is done or not and records the value.
     *
     * @return bool True if it has a state of 'done'.
     *
     * @uses ReturnVisitController::oldSession Checks the state from this array.
     * @uses ReturnVisitController::done Sets the done value here.
     */
    public function isDone()
    {
        if ($this->oldSession['state'] === 'done') {
            $this->done = true;

            return true;
        }

        return false;
    }

    /**
     * Updates $_SESSION and sends user to start the next Experiment.
     *
     * @param Pathfinder $pathfinder The Experiment's Pathfinder object.
     * @param User       $user       The participant's User object.
     */
    public function reloadToExperiment(Pathfinder $pathfinder, User $user)
    {
        $_SESSION = $this->oldSession;
        $pathfinder->setDefaultsCopy($_SESSION['Pathfinder']);
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
        $_SESSION['Start Time'] = time();
        $_SESSION['state'] = 'exp';
        unset($_SESSION['next']);
        header("Location: $this->expLink");
        exit;
    }

    /**
     * Removes 'next' from $_SESSION and sends user to done.
     */
    public function reloadToDone()
    {
        $_SESSION = $this->oldSession;
        unset($_SESSION['next']);
        header("Location: $this->doneLink");
        exit;
    }
    /**
     * Checks if this participate is ready to login to the next session.
     * Return time is based on the 'Min Time' and 'Max Time' cells from the
     * `NewSession` line of the procedure. In the case that it is not time to
     * return then it sets the time until return (earlyMsg) or a sorry message
     * for when over max time (lateMsg).
     *
     * @return bool True if it is time to start the next session, else false.
     *
     * @uses ReturnVisitController::earlyMsg Shows this string to early returns.
     * @uses ReturnVisitController::earlyMsg Shows this string to early returns.
     */
    public function isTimeToReturn()
    {
        $now = time();
        $minRet = $this->oldSession['Min Return'];
        $maxRet = $this->oldSession['Max Return'];

        // determines if it is too late to return
        $early = $late = false;

        if (!empty($maxRet) && $now > $maxRet) {
            $late = true;
            $this->lateMsg = 'Sorry, you have returned too late to participate.';
        }

        if ($now < $minRet) {
            $early = true;
            $dif = $minRet - $now;
            $remaining = durationFormatted($dif);
            $this->earlyMsg = 'You are too eary to participate in this part. You'
                    ."can return in $remaining to start this next part.";
        }

        return ($late === true) || ($early === true);
    }

    /**
     * Shows either the late or early return time error message.
     * Each var is initiated with each class instance but the value is only
     * modified if $this->timeToReturn() finds it is too early or too late to
     * come back.
     *
     * @uses ReturnVisitController::earlyMsg Displays this value if it is set.
     * @uses ReturnVisitController::lateMsg Displays this value if it is set.
     */
    public function getTimeProblem()
    {
        if ($this->earlyMsg !== null) {
            return $this->earlyMsg;
        }

        if ($this->lateMsg !== null) {
            return $this->lateMsg;
        }
    }

    /**
     * Returns an array of information about this class. Array is optionally
     * converted to an HTML formatted string.
     *
     * @param bool $asString Set true to convert to an HTML formatted string.
     */
    public function debug($asString = true)
    {
        $info = array(
            'sessionStoragePath' => $this->sessionStoragePath,
            'doneLink' => $this->doneLink,
            'early' => $this->earlyMsg,
            'late' => $this->lateMsg,
        );

        if ($asString === true) {
            $str = '<ul>';
            foreach ($info as $key => $val) {
                $str .= "<li><strong>{$key}</strong><br>{$val}</li>";
            }
            $info = $str.'</ul>';
        }

        return $info;
    }

    /**
     * Gets the session number.
     *
     * @return int The session number, or one if current session number is not
     *             set or not numeric.
     *
     * @uses ReturnVisitController::sessionNumber Returns this value.
     */
    public function getSession()
    {
        return is_numeric($this->sessionNumber) ? $this->sessionNumber : 1;
    }

    /**
     * Returns the condition information from the previous session.
     *
     * @return array Previous condition information.
     */
    public function oldCondition()
    {
        return $this->oldSession['Condition'];
    }
}
