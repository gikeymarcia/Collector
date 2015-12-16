<?php
    // if not set then set to 0
    $min_time = empty($min_time) ? 0 : $min_time;
    $max_time = empty($max_time) ? 0 : $max_time;

    // turn formatted text into seconds
    // expecting strings that look like "5d:10h:15m:8s"
    // will return 0 if the string doesn't match the formatting
    $min_time = durationInSeconds($min_time);
    $max_time = durationInSeconds($max_time);

    // setting when people are allowed to return to the study
    $_SESSION['Min Return'] = time() + $min_time;
    $_SESSION['Max Return'] = time() + $max_time;
    if ($max_time == 0) {
        $_SESSION['Max Return'] = false;
    }

    // writing a line of data for this NewSession trial
    $data = array();
        $year  = date('Y');
        $month = date('m');
        $day   = date('d');
        $hour  = date('h');
        $min   = date('i');
        $secs  = date('s');
    $date = "$year.$month.$day--$hour:$min:$secs";
    $data['Time']       = $date;
    $data['Min Return'] = $_SESSION['Min Return'];
    $data['Max Return'] = $_SESSION['Max Return'];
    $_EXPT->recordResponses($data);


    // updating state as coming from a newsession and sending to done.php
    $_SESSION['state'] = 'break';
    if (!empty($next)) {
        $_SESSION['next'] = $next;
    }
    header("Location: " . $_PATH->get("Done"));
    exit;