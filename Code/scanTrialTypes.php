<?php
    $scan = scandir('TrialTypes');
    $trialTypes = array();
    
    foreach ($scan as $entry) {
        if ($entry[0] === '.') { continue; }
        if (is_dir('TrialTypes/' . $entry)) {
            $temp = array();
            $subScan = scandir('TrialTypes/' . $entry);
            foreach ($subScan as $subEntry) {
                $lower = strtolower($subEntry);
                if ($lower === 'trial.php') {
                    $temp['trial']   = 'TrialTypes/' . $entry . '/' . $subEntry;
                } elseif ($lower === 'scoring.php') {
                    $temp['scoring'] = 'TrialTypes/' . $entry . '/' . $subEntry;
                }
            }
            if (isset($temp['trial'])) {
                if (!isset($temp['scoring'])) { $temp['scoring'] = 'scoring.php'; }
                $trialTypes[ strtolower($entry) ] = $temp;
            }
        } elseif (strtolower(substr($entry, -4)) === '.php') {
            $type = strtolower(substr($entry, 0, -4));
            $trialTypes[$type]['trial']   = 'TrialTypes/' . $entry;
            $trialTypes[$type]['scoring'] = 'scoring.php';
        }
    }
?>