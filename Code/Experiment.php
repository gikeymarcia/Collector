<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */

require 'initiateCollector.php';
require $_PATH->get('Experiment Require');

// check that the state is set    
if (empty($_SESSION['state'])) {
    // get out if you're not supposed to be here
    $root = $_PATH->get('root');
    header("Location: $root");
    exit;
}

// this only happens once, so that refreshing the page doesn't do anything, and 
// recording a new line of data is the only way to update the timestamp
if (!isset($_SESSION['Timestamp'])) {
    $_SESSION['Timestamp'] = microtime(true);
}

// start an output buffer, so that if we need to redirect, we aren't
// blocked by previously sent content
ob_start();

// setting up easier to use and read aliases(shortcuts) of $_SESSION data
$condition = &$_EXPT->condition;
$currentPos = &$_EXPT->position;
$currentPost = &$_EXPT->postNumber;

if ($_EXPT->isDone()) {
    gotoDone();
}

if (!isset($_EXPT->responses[$currentPos])) {
    $_EXPT->responses[$currentPos] = array();
}

/*
 *  CREATE CURRENT TRIAL
 */
// also create aliases to proc and stim data
$currentTrial = $_EXPT->getTrial();
$procedure = $currentTrial['Procedure'];
$stimuli = $currentTrial['Stimuli'];

/*
 *  CREATE ALIASES
 */
$trialValues = $_EXPT->prepareAliases();

// do not overwrite with aliases, in case they have a "condition" column
extract($trialValues, EXTR_SKIP);

// from this point on, we should use variables that have at least
// one capital letter in them, so that we can be guaranteed not
// to overwrite any aliases set for this trial. All aliases
// will be lowered and spaces replaced with underscores
// e.g., column "Trial Type" is now $trial_type, so dont override the $trial_type variable

/*
 *  GET TRIAL TYPE FILES
 */
$trial_type = strtolower($trial_type);

$trialFiles = getTrialTypeFiles($trial_type);
if (isset($trialFiles['script'])) {
    $addedScripts = array($trialFiles['script']);
}
if (isset($trialFiles['style'])) {
    $addedStyles = array($trialFiles['style']);
}

if (isset($trialFiles['helper'])) {
    include $trialFiles['helper'];
}

/*
 *  TEXT REPLACEMENT
 */
// update $text containing $columnNames with values from files
if (isset($text)) {
    $textSearch = array();
    $textReplace = array();
    foreach ($trialValues as $trialCol => $trialVal) {
        $textSearch[] = '$'.$trialCol;
        $textReplace[] = $trialVal;
    }
    $text = str_replace($textSearch, $textReplace, $text);
} else {
    $text = '';
}
unset($trialCol, $trialVal);

/*
 *  TIMING
 */
// override time in debug mode, use standard timing if no debug time is set
if ($_SESSION['Debug'] == true && $_SETTINGS->debug_time != '') {
    $max_time = $_SETTINGS->debug_time;
}

if (!isset($min_time)) {
    $min_time = 'not set';
}
if (!isset($defaultMaxTime)) {
    $defaultMaxTime = null;
}

// set class for input form (shows or hides 'submit' button)
$max_time = strtolower($max_time);
if ($max_time === 'computer' || $max_time === 'default') {
    $max_time = is_numeric($defaultMaxTime) ? $defaultMaxTime : 'user';
}
if (!is_numeric($max_time)) {
    $max_time = 'user';
}
$formClass = ($max_time === 'user') ? 'UserTiming' : 'ComputerTiming';

/*
 *  PREPARE TO DISPLAY
 */
$postTo = $_PATH->get('Experiment Page');
$trialFail = false; // used to show diagnostic information when a trial fails

$title = 'Experiment';
$_dataController = 'experiment';
$_dataAction = $trial_type;

/*
 * RECORD DATA
 *
 * Whenever experiment.php finds $_POST data, it will try to store that data
 * immediately, rather than simply holding it through the trial.
 * If the main trial and all post trials are completed, the data will
 * be written using the recordTrial() function.
 */
if ($_POST !== array()) {
    require $trialFiles['scoring'];

    if (!isset($data)) {
        $data = $_POST;
    }
    $_EXPT->recordResponses($data);

    $nextTrialIndex = $_EXPT->getNextTrialIndex();
    if ($nextTrialIndex === false || $nextTrialIndex[0] > $currentPos) {
        recordTrial();
    }

    // go to the next trial or end experiment
    $nextTrial = $_EXPT->getNextTrialIndex();
    if ($nextTrial !== false) {
        $_EXPT->position = $nextTrial[0];
        $_EXPT->postNumber = $nextTrial[1];
        header('Location: '.$_PATH->get('Experiment Page'));
        exit;
    }

    ++$_EXPT->position;
    if ($_EXPT->isDone()) {
        gotoDone();
    }
}

/*
 *  DISPLAY
 */
require $_PATH->get('Header');

// actually include the trial type display file here
if ($trialFiles['display']): ?>
<form class="experimentForm <?= $formClass; ?> invisible" action="<?= $postTo; ?>" method="post" id="content" autocomplete="off">
  <?php include $trialFiles['display'] ?>
  
  <?php if ($_SESSION['Debug']) : ?>
  <button type="submit" style="position: absolute; top: 50px; right: 50px;"onclick="$('form').submit()">Debug Submit!</button>
  <?php endif; ?>
  
  <input id="RT"       name="RT"      type="hidden"  value="-1"/>
  <input id="RTfirst"  name="RTfirst" type="hidden"  value="-1"/>
  <input id="RTlast"   name="RTlast"  type="hidden"  value="-1"/>
  <input id="Focus"    name="Focus"   type="hidden"  value="-1"/>
</form>

<?php else: ?>
<h2>Could not find the following trial type: <strong><?= $trial_type; ?></strong></h2>
<p>Check your procedure file to make sure everything is in order. All information about this trial is displayed below.</p>

<!-- default trial is always user timing so you can click 'Done' and progress through the experiment -->
<div>
  <form name="UserTiming" class="UserTiming" action="<?= $postTo; ?>" method="post" autocomplete="off">
    <input id="RT"      name="RT"      type="hidden" value="-1"/>
    <input id="RTfirst" name="RTfirst" type="hidden" value="-1"/>
    <input id="RTlast"  name="RTlast"  type="hidden" value="-1"/>
    <input id="Focus"   name="Focus"   type="hidden" value="-1"/>
    <input class="collectorButton collectorAdvance" id="FormSubmitButton" type="submit" value="Done" />
  </form>
</div>

<?php
$trialFail = true;
$maxTime = 'user';
endif; ?>

<!-- hidden field that JQuery/JavaScript uses to check the timing to $postTo -->
<div id="maxTime" class="hidden"> <?= $max_time; ?> </div>
<div id="minTime" class="hidden"> <?= $min_time; ?> </div>
   
<?php
/*
 *  Diagnostics
 */
if (($_SETTINGS->trial_diagnostics == true) || ($trialFail == true)) {
    $_EXPT->showTrialDiagnostics();
}

/*
 *  PRECACHE
 */
echo $_EXPT->getPrecache();

require $_PATH->get('Footer');

ob_end_flush();
