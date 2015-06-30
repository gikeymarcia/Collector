<?php
    if (!function_exists('scanTrialTypes')) {
    
        function scanTrialTypes() {
            $fileLoc = 'Code/fileLocations.php';                // we need this file to find where out trial types are being kept
            $_rootF = '';                                       // $_rootF helps find PHP files
            $i = 0;
            while (!is_file($_rootF . $fileLoc)) {
                $_rootF .= '../';
                if ($i > 15) { return FALSE; }
                ++$i;
            }
            // get the location of the trial types
            include $_rootF . $fileLoc;
            
            
            // prepare list of directories to search for trial types
            $scanDirs = array(
                $_rootF . $codeF    . $trialF,
                $_rootF . $expFiles . $custTTF
            );
            
            $trialTypes = array();
            
            // list of files that each trial type might have
            $fileTypes = array(
                'display' => $trialTypeDisplay,
                'scoring' => $customScoring,
                'style'   => $customStyle,
                'script'  => $customScript
            );
            
            $defaultScoringWithPath = fileExists($_rootF . 'Code/' . $defaultScoring);
            
            
            // loop through both the Code/ and the Experiment/ folders
            foreach ($scanDirs as $dir) {
            
                // if there isn't a trial types folder, skip this branch
                if (!is_dir($dir)) { continue; }
                $scan = scandir($dir);
                
                foreach ($scan as $entry) {
                    if ($entry[0] === '.')      { continue; }   // don't use the '.' or the '..' entries
                    if (!is_dir($dir . $entry)) { continue; }   // all trial types should be in their own directory, such as Instruct/
                    
                    $thisType = strtolower($entry);
                    
                    foreach ($fileTypes as $fileType => $filename) {
                        $file = fileExists($dir . $entry . '/' . $filename);
                        if ($file !== FALSE) {
                            $trialTypes[$thisType][$fileType] = $file;
                        }
                    }
                    
                }
            
            }
            
            foreach ($trialTypes as $type => $files) {
                if (      !isset($trialTypes[$type]['display'])) {
                    unset($trialTypes[$type]);
                } elseif (!isset($trialTypes[$type]['scoring'])) {
                    $trialTypes[$type]['scoring'] = $defaultScoringWithPath;
                }
            }
            
            return $trialTypes;
        }
        
    }
    
    return scanTrialTypes();
