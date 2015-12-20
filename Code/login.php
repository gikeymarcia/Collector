<?php
/*  Collector
    A program for running experiments on the web
 */
require 'initiateCollector.php';

// reset session to remove information from any previous login attempts
$_SESSION = array();
$_SESSION['state'] = 'init';

// initiate the object that finds files for us
$_PATH = new Pathfinder($_SESSION['Pathfinder']);

// load shuffle functions we will use later
require $_PATH->get('Shuffle Functions');

// establish which experiment is active
$_SESSION['Current Collector'] = $_PATH->get('root', 'url');
$currentExp = filter_input(INPUT_GET, 'CurrentExp', FILTER_SANITIZE_STRING);
$current = ($currentExp === null) ? '' : $currentExp;
if (!in_array($current, getCollectorExperiments())) {
    // requested experiment does not exist: send back to index
    header('Location: '.$_PATH->get('root'));
    exit;
}

// tell pathfinder the current experiment and load common/experiment settings
$_PATH->setDefault('Current Experiment', $current);
$_SETTINGS = getCollectorSettings();

/*
 * Login objects
 */
// error handler
$errors = new ErrorController();

// user validator
$username = filter_input(INPUT_GET, 'Username', FILTER_SANITIZE_EMAIL);
$user = new User($username, $errors);
$user->feedPathfinder($_PATH);

// debug handler
$debug = new DebugController(
    $user->getUsername(),
    $_SETTINGS->debug_name,
    $_SETTINGS->debug_mode
);
// @todo change feedPathfinder to return a value that Pathfinder will accept
$debug->feedPathfinder($_PATH); // changes data directory if debug mode is on 
$debug->toSession(); // sets $_SESSION['Debug'] to a bool

// condition controller
$cond = new ConditionController(
    $_PATH->get('Conditions'),
    $_PATH->get('Counter'),
    $errors,
    $_SETTINGS->hide_flagged_conditions
);
$selectedCondition = filter_input(INPUT_GET, 'Condition', FILTER_SANITIZE_STRING);
$cond->setSelected($selectedCondition);

// is this user ineligible to participate in the experiment?
if ($_SETTINGS->check_elig == true) {
    include $_PATH->get('check');
}

/*
 * Returning participants
 */
$revisit = new ReturnVisitController(
    $_PATH->get('json'),
    $_PATH->get('Done'),
    $_PATH->get('Experiment Page')
);

if ($revisit->isReturning()) {
    if ($revisit->isDone()) {
        $revisit->reloadToDone();
    }

    if (!$revisit->isTimeToReturn()) {
        exit($revisit->getTimeProblem());
    }

    // update with info from previous login and send to experiment
    $revisit->reloadToExperiment($_PATH, $user);
}

// stop people who are trying to return that we don't know about
// ($revisit->reloadToExperiment would have sent them on if we did)
$returning = filter_input(INPUT_GET, 'returning', FILTER_SANITIZE_STRING);
if ($returning !== null) {
    exit('Could not find the next part of the experiment for '.$user->getUsername());
}

/*
 * Non-returning participants
 */
// set user's condition and modify paths based on assigned condition
$cond->assignCondition();
$_PATH->setDefault('Condition Index', $cond->getAssignedIndex());

$procedure = new Procedure(
    $_PATH->get('Procedure Dir'),
    $cond->allProc(),
    $errors
);
$stimuli = new Stimuli(
    $_PATH->get('Stimuli Dir'),
    $cond->allStim(),
    $errors
);

$status = new StatusController();
$status->updateUser(
    $user->getUsername(),
    $user->getID(),
    $user->getOutputFile(),
    $user->getSession()
);
$status->setConditionInfo(
    $cond->get()
);
$status->setPaths(
    $_PATH->get('Status Begin Data'),
    $_PATH->get('Status End Data')
);
$status->writeBegin();
$_SESSION['Status'] = serialize($status);

// check if procedure and stimuli files have unique column names
$procedure->checkOverlap($stimuli->getKeys(true));

$procedure->shuffle();
$stimuli->shuffle();

#### Trial Validation
require $_PATH->get('Trial Validator Require');

######## Feed stuff to login #######
$_SESSION['Username'] = $user->getUsername();
$_SESSION['ID'] = $user->getID();
$_SESSION['Session'] = $user->getSession();
$_SESSION['Start Time'] = time();

// access stimuli, procedure, and condition arrays using $_EXPT->[name]
$_EXPT = new Experiment(
            $stimuli->getShuffled(),
            $procedure->getShuffled(),
            $cond->get()
        );
$_SESSION['_EXPT'] = $_EXPT;

####################################

if ($errors->arePresent()) {
    echo $errors;
    echo "<div>
            Oops, something has gone wrong. Email the experimenter at <b>$_SETTINGS->experimenter_email</b><br>
            <button type='button' onClick='window.location.reload(true);'>Click here to refresh</button>
          </div>";
    exit;
}

$_SESSION['state'] = 'exp';
$experiment = $_PATH->get('Experiment Page');
header("Location: $experiment");
exit;
