<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
require 'initiateCollector.php';

/*
 *  redirect people who aren't supposed to be here
 */
if (empty($_SESSION['state'])) {
    $bounceTo = '../';
} elseif ($_SESSION['state'] === 'exp') {
    $bounceTo = $_PATH->get('Experiment Page');
}
if (isset($bounceTo)) {
    header("Location: $bounceTo");
    exit;
}

/*
 *  Save the $_SESSION array
 */
$validStates = array('break' => 0, 'done' => 0);
if (isset($validStates[$_SESSION['state']])) {
    $status = unserialize($_SESSION['Status']);
    $status->writeEnd($_SESSION['Start Time']);

    // preparing $_SESSION for the next run
    if ($_SESSION['state'] == 'break') {
        //        $_SESSION['state'] = 'return';
        // increment counter so next session will begin after the NewSession
        ++$_EXPT->position;
        // increment session # so next login will labeled as the next session
        ++$_SESSION['Session'];
        // generate a new ID (for next login)
        $_SESSION['ID'] = randString();
    }

    // encode the entire $_SESSION array as a json string
    $sessionString = base64_encode(serialize($_SESSION));
    $sessionStoragePath = $_PATH->get('Session Storage');

    if (!is_dir($_PATH->get('Session Storage Dir'))) {
        // make the folder if it doesn't exist
        mkdir($_PATH->get('Session Storage Dir'), 0777, true);
    }
    file_put_contents($sessionStoragePath, $sessionString);
}

/*
 *  Redirect if multisession
 */
if (!empty($_SESSION['next'])) {
    $next = $_SESSION['next'];
    $username = urlencode($_SESSION['Username']);
    $nextLink = "http://{$_SETTINGS->next_experiment}/login.php?Username={$username}&Condition=Auto";
    header("Location: {$nextLink}");
    exit;
}

/*
 * Display
 */
$email = $_SETTINGS->experimenter_email;
$currentExperiment = $_PATH->getDefault('Current Experiment');
$verification_code = "{$_SETTINGS->verification}-{$_SESSION['ID']}";
$title = 'Done!';
$mailto = "{$email}?Subject=Comments%20on%20{$currentExperiment}";

require $_PATH->get('Header');
?>
    
<style>
  #content {
    width: 500px;
    text-rendering: optimizeLegibility;
  }
</style>
<form id="content">
  <h2>Thank you for your participation!</h2>
  
  <p>If you have any questions about the experiment please email 
    <a href='mailto:<?= $mailto ?>' target='_top'> <?= $email ?> </a>
  </p>
  
  <?php if ($_SETTINGS->verification !== ''): ?>
  <h3>Your verification code is: <?= $verification_code ?></h3>
  <?php endif; ?>
</form>

<?php
require $_PATH->get('Footer');
