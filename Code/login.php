<?php
/*  Collector
    A program for running experiments on the web
 */
require 'initiateCollector.php';

// reset session to remove information from any previous login attempts
$_SESSION = array();
$_SESSION['state'] = 'init';

// initiate the object that finds files for us
$_PATH = $_SESSION['_PATH'] = new Pathfinder();

// load shuffle functions we will use later
require $_PATH->get('Shuffle Functions');

// establish which experiment is active
$_SESSION['Current Collector'] = $_PATH->get('root', 'url');
$currentExp = filter_input(INPUT_GET, 'CurrentExp', FILTER_SANITIZE_STRING);
$current = ($currentExp === null) ? '' : $currentExp;
if (!in_array($current, getCollectorExperiments())) {
    // requested experiment does not exist: send back to index
    header('Location: ' . $_PATH->get('root'));
    exit;
}

// tell pathfinder the current experiment and load common/experiment settings
$_PATH->setDefault('Current Experiment', $current);
$_SESSION['settings'] = new Collector\Settings(
    $_PATH->get('Common Settings'),
    $_PATH->get('Experiment Settings'),
    $_PATH->get('Password')
);
$_SETTINGS = &$_SESSION['settings'];

/*
 * Login objects
 */
// error handler
$errors = new Collector\ErrorController();

// user validator
$username = filter_input(INPUT_GET, 'Username', FILTER_SANITIZE_EMAIL);
$user = new Collector\User($username, $errors);
$user->feedPathfinder($_PATH);

// debug handler
$debug = new Collector\DebugController(
    $user->getUsername(),
    $_SETTINGS->debug_name,
    $_SETTINGS->debug_mode
);
// @todo change feedPathfinder to return a value that Pathfinder will accept
$debug->feedPathfinder($_PATH); // changes data directory if debug mode is on
$debug->toSession(); // sets $_SESSION['Debug'] to a bool

// condition controller
$cond = new Collector\ConditionController(
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
$revisit = new Collector\ReturnVisitController(
    $_PATH->get('Session Storage'),
    $_PATH->get('Done'),
    $_PATH->get('Experiment Page')
);

if ($revisit->isReturning()) {
    if ($revisit->isDone()) {
        $revisit->reloadToDone();
    }

    if ($revisit->isTimeToReturn()) {
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

// retrieve and store status information
$status = new Collector\StatusController();
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

// load and prepare procedure and stimuli
$procedure = new Collector\Procedure(
    $_PATH->get('Procedure Dir'),
    $cond->allProc(),
    $errors
);
$stimuli = new Collector\Stimuli(
    $_PATH->get('Stimuli Dir'),
    $cond->allStim(),
    $errors
);
$procedure->checkOverlap($stimuli->getKeys(true));
$procedure->shuffle();
$stimuli->shuffle();

$_SIDE = new Collector\SideData();

/*
 *  Create the Experiment Object
 */
$_EXPT = Collector\ExperimentFactory::create(
    $cond->get(), $procedure->getShuffled(), $stimuli->getShuffled(), $_PATH
)->warm();

// validate and show errors
$validationErrors = $_EXPT->validate();
if (!empty($validationErrors)) {
    foreach ($validationErrors as $error) {
        $errors->add($error['message'] . " Error found in MainTrial " . 
            $error['info']['position'] . " at post position " . 
            $error['info']['postPosition'] . ". (positions are 0-indexed)");
    }
}

/*
 * Stop on errors
 */
if ($errors->arePresent() || !empty($validationErrors)) {
    require $_PATH->get('Header');
    echo "
      <div style='max-width:600px;'>
        <p>{$errors}<br>
        <p class='textcenter'>
          Oops, something has gone wrong. Email the experimenter at <b>$_SETTINGS->experimenter_email</b>
          <br><br>
          <button class='collectorButton' onClick='window.location.reload(true);'>Refresh</button>
          <button class='collectorButton' onClick='window.location.href=\"{$_PATH->get('Current Experiment')}\";'>Back to Login</button>
      </div>";
    datadump($_EXPT);
    require $_PATH->get('Footer');
    exit;
}

/*
 * Populate Session and start experiment
 */
$_SESSION['Username'] = $user->getUsername();
$_SESSION['ID'] = $user->getID();
$_SESSION['Session'] = $user->getSession();
$_SESSION['Start Time'] = time();
$_SESSION['Status'] = serialize($status);
$_SESSION['_EXPT'] = $_EXPT;
$_SESSION['_SIDE'] = $_SIDE;
$_SESSION['state'] = 'exp';

header("Location: " . $_PATH->get('Experiment Page'));
exit;
