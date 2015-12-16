<?php


/**
 * Sets the state to done and sends to 'Done' page.
 * @global Pathfinder $_PATH
 * @return void
 */
function gotoDone() {
    global $_PATH;

    $_SESSION['state'] = 'done';
    header('Location: ' . $_PATH->get('Done'));
    exit;
}

/**
 * Records responses for entire proc row into the output csv.
 * @param  array $extraData [optional] extra data to add to this row of output, \
 *                          using the keys as columns.
 * @param  int   $pos       [optional] The trial to record.
 * @return void
 */
function recordTrial(array $extraData = array(), $pos = null) {
    global $_EXPT;
    global $_PATH;

    if ($pos === null) { $pos = $_EXPT->position; }

    #### Calculating time difference from current to last trial
    $oldTime = $_SESSION['Timestamp'];
    $_SESSION['Timestamp'] = microtime(true);
    $timeDif = $_SESSION['Timestamp'] - $oldTime;
    
    #### Writing to data file
    $data = array(
        'Username'       =>  $_SESSION['Username'],
        'ID'             =>  $_SESSION['ID'],
        'ExperimentName' =>  $_PATH->getDefault('Current Experiment'),
        'Session'        =>  $_SESSION['Session'],
        'Trial'          =>  $pos,
        'Date'           =>  date("c"),
        'TimeDif'        =>  $timeDif,
    );
    
    $trialData = $_EXPT->getTrialRecord($pos);
    
    foreach ($trialData as $category => $values) {
        foreach ($values as $col => $val) {
            $data[$category . '_' . $col] = $val;
        }
    }
    
    if (!is_array($extraData)) {
        $extraData = array('Extra Data' => (string) $extraData);
    }
    foreach ($extraData as $header => $datum) {
        $data[$header] = $datum;
    }
    
    // record line into output CSV
    arrayToLine($data, $_PATH->get('Experiment Output'));
}

