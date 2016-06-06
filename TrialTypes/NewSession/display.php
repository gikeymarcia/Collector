<?php
// if not set then set to 0, 
// otherwise convert formatted times like "5d:10h:15m:8s" to seconds
$minTime = $_EXPT->get('min time');
$maxTime = $_EXPT->get('max time');
$min_time = empty($minTime) ? 0 : durationInSeconds($minTime);
$max_time = empty($maxTime) ? 0 : durationInSeconds($maxTime);

// declare when people are allowed to return to the study
$_SESSION['Min Return'] = time() + $min_time;
$_SESSION['Max Return'] = time() + $max_time;
if ($max_time == 0) {
    $_SESSION['Max Return'] = false;
}

// get date and time
$year = date('Y');
$month = date('m');
$day = date('d');
$hour = date('h');
$min = date('i');
$secs = date('s');
$date = "$year.$month.$day--$hour:$min:$secs";

// write the line of data for this NewSession trial
$data = array();
$data['Time'] = $date;
$data['Min Return'] = $_SESSION['Min Return'];
$data['Max Return'] = $_SESSION['Max Return'];
$_EXPT->record($data);

// update state as coming from a newsession and then send to done.php
$_SESSION['state'] = 'break';
if (!empty($next)) {
    $_SESSION['next'] = $next;
}
header('Location: '.$_PATH->get('Done'));
exit;
