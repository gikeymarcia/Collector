<?php
    if (!function_exists('scanTrialTypes')) {
    
        function scanTrialTypes() {
            $i       = 0;
            $_rootF  = '';
            $fileLoc = 'Code/fileLocations.php';
            while (!is_file($_rootF . $fileLoc)) {
                ++$i;
                if ($i > 15) { return FALSE; }
                $_rootF .= '../';
            }
            include $_rootF . $fileLoc;
            
            $scanDirs = array(
                $_rootF . $expFiles . $custTTF,
                $_rootF . $codeF    . $trialF
            );
            
            $trialTypes = array();
            
            foreach ($scanDirs as $dir) {
            
                if (!is_dir($dir)) { continue; }
                $scan = scandir($dir);
                
                foreach ($scan as $entry) {
                    if ($entry[0] === '.') { continue; }
                    if (is_dir($dir . $entry)) {
                        $type = strtolower($entry);
                        if (isset($trialTypes[$type])) { continue; }
                        $temp = array();
                        $subScan = scandir($dir . $entry);
                        foreach ($subScan as $subEntry) {
                            $lower = strtolower($subEntry);
                            if ($lower === 'trial.php') {
                                $temp['trial']   = $dir . $entry . '/' . $subEntry;
                            } elseif ($lower === 'scoring.php') {
                                $temp['scoring'] = $dir . $entry . '/' . $subEntry;
                            }
                        }
                        if (isset($temp['trial'])) {
                            if (!isset($temp['scoring'])) { $temp['scoring'] = $_rootF . $codeF . 'scoring.php'; }
                            $trialTypes[$type] = $temp;
                        }
                        
                    } elseif (strtolower(substr($entry, -4)) === '.php') {
                        $type = strtolower(substr($entry, 0, -4));
                        if (isset($trialTypes[$type])) { continue; }
                        $trialTypes[$type]['trial']   = $dir . $entry;
                        $trialTypes[$type]['scoring'] = $_rootF . $codeF . 'scoring.php';
                    }
                }
            
            }
            
            return $trialTypes;
        }
        
    }
    
    return scanTrialTypes();
?>