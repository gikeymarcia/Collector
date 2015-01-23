<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    if ($findingKeys) {
        return array('JOL', 'RT', 'RTkey', 'RTlast');
    }
    $data = $_POST;
    /*
     * Q: Why are we using $data instead of setting values directly into $_SESSION['Trials']?
     * A: $data holds all scoring information and once scoring is complete $data is merged into $_SESSION['Trials'][$currentPos]['Response']
     *    Using $data as a middle man is needed becasue if scoring is happening on a `Post # Trial Type` then when merging $data back into $currentTrial['Respnonse']
     *    the program will automatically prepend each stored key with 'post#_' (e.g., $data['RT'] would be merged as $data['post#_RT] iF scoring is happening for a 'Post 1 Trial Type')
     */
?>