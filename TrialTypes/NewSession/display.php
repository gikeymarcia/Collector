<?php
// if not set then set to 0, 
// otherwise convert formatted times like "5d:10h:15m:8s" to seconds
$min_time = empty($min_time) ? 0 : durationInSeconds($min_time);
$max_time = empty($max_time) ? 0 : durationInSeconds($max_time);

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
$_EXPT->recordResponses($data);

// update state as coming from a newsession and then send to done.php
$_SESSION['state'] = 'break';
if (!empty($next)) {
    $_SESSION['next'] = $next;
}
header('Location: '.$_PATH->get('Done'));
exit;
