<?php
/**
 * Sets the state to done and sends to 'Done' page.
 *
 * @global Pathfinder $_PATH
 */
function gotoDone()
{
    global $_PATH;

    $_SESSION['state'] = 'done';
    header('Location: ' . $_PATH->get('Done'));
    exit;
}

/**
 * Records responses for entire proc row into the output csv.
 *
 * @param array $extraData [Optional] Extra data to add to this row of output,
 *                         using the keys as columns.
 * @param int   $pos       [Optional] The trial to record.
 */
function recordTrial(Collector\Trial $trial, array $extraData = array(), $pos = null)
{
    global $_PATH;
    
    // calculate time difference from current to last trial
    $oldTime = $_SESSION['Timestamp'];
    $_SESSION['Timestamp'] = microtime(true);
    $timeDif = $_SESSION['Timestamp'] - $oldTime;

    // write to data array
    $data = array(
        'Username' => $_SESSION['Username'],
        'ID' => $_SESSION['ID'],
        'ExperimentName' => $_PATH->getDefault('Current Experiment'),
        'Session' => $_SESSION['Session'],
        'Trial' => $trial->position,
        'Date' => date('c'),
        'TimeDif' => $timeDif,
    );

    foreach ($trial->export() as $name => $trialPart) {
        $data = placeData($trialPart, $data, "$name * ");
    }
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $data = placeData($val, $data, "$key * ");
            unset($data[$key]);
        }
    }
    if (!empty($extraData)) {
        $data = placeData($extraData, $data, 'extra * ');
    }
    
    // record line into output CSV
    arrayToLine($data, $_PATH->get('Experiment Output'));
}
