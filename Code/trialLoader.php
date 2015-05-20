<?php
    if (!isset($_GET['ready']) AND $_POST === array()) {
        echo '<div style="text-align: center;">Please select a trial type. . . .</div>';
        exit;
    }
    
    if (isset($_GET['ready'])) {
        
        include 'experiment.php';
        
        ?>
        <script>
            $(window).load(function(){
                $("form").attr("action", "trialLoader.php");
            });
        </script>
        <?php
        
    } else {
    
        include 'initiateCollector.php';
        include 'Header.php';
        
        
        
        function arrayToLineBroken ($row, $fileName, $d = NULL, $encodeUtf8ToWin = TRUE) {
            if ($d === NULL) {
                $d = isset ($_SESSION['OutputDelimiter']) ? $_SESSION['OutputDelimiter'] : ",";
            }
            if (!is_dir(dirname($fileName))) {
                // mkdir(dirname($fileName), 0777, true);
            }
            if ($encodeUtf8ToWin) {
                if (mb_detect_encoding(implode('', $row), 'UTF-8', TRUE)) {
                    foreach ($row as &$datum) {
                        $datum = mb_convert_encoding($datum, 'Windows-1252', 'UTF-8');
                    }
                    unset($datum);
                }
            }
            foreach ($row as &$datum) {
                $datum = str_replace(array("\r\n", "\n", "\t", "\r", chr(10), chr(13)), ' ', $datum);
            }
            unset($datum);
            /*
            $fileTrue = fileExists($fileName);
            if (!$fileTrue) {
                $file = fopen($fileName, "w");
                fputcsv($file, array_keys($row), $d);
                fputcsv($file, $row, $d);
            } else {
                $file = fopen($fileTrue, "r+");
                $headers = array_flip(fgetcsv($file, 0, $d));
                $newHeaders = array_diff_key($row, $headers);
                if ($newHeaders !== array()) {
                    $headers = $headers+$newHeaders;
                    $oldData = stream_get_contents($file);
                    rewind($file);
                    fputcsv($file, array_keys($headers), $d);
                    fwrite($file, $oldData);
                }
                fseek($file, 0, SEEK_END);
                $row = SortArrayLikeArray($row, $headers);
                fputcsv($file, $row, $d);
            }
            fclose($file);
            */
            return $row;
        }
        
        
        
        #### Copied from experiment.php, update as needed ####
        
        if (!isset($_SESSION['Timestamp'])) {
            $_SESSION['Timestamp'] = microtime(TRUE);
        }
    
    
    
        function recordTrial($extraData = array(), $exitIfDone = TRUE, $advancePosition = TRUE) {

            #### setting up aliases (for later use)
            $currentPos   =& $_SESSION['Position'];
            $currentTrial =& $_SESSION['Trials'][$currentPos];
            
            global $experimentName;


            #### Calculating time difference from current to last trial
            $oldTime = $_SESSION['Timestamp'];
            $_SESSION['Timestamp'] = microtime(TRUE);
            $timeDif = $_SESSION['Timestamp'] - $oldTime;
            
            
            #### Writing to data file
            $data = array(  'Username'              =>  $_SESSION['Username'],
                            'ID'                    =>  $_SESSION['ID'],
                            'ExperimentName'        =>  $experimentName,
                            'Trial'                 =>  $_SESSION['Position'],
                            'Date'                  =>  date("c"),
                            'TimeDif'               =>  $timeDif,
                            'Condition Number'      =>  $_SESSION['Condition']['Number'],
                            'Stimuli File'          =>  $_SESSION['Condition']['Stimuli'],
                            'Order File'            =>  $_SESSION['Condition']['Procedure'],
                            'Condition Description' =>  $_SESSION['Condition']['Condition Description']
                          );
            foreach ($currentTrial as $category => $array) {
                $data += AddPrefixToArray($category . '*', $array);
            }
            
            if (!is_array($extraData)) {
                $extraData = array($extraData);
            }
            foreach ($extraData as $header => $datum) {
                $data[$header] = $datum;
            }
            
         // $writtenArray = arrayToLine($data, $_SESSION['Output File']);                                       // write data line to the file
            $writtenArray = arrayToLineBroken($data, '');                                       // write data line to the file
            ###########################################


            // progresses the trial counter
            if ($advancePosition) {
                $currentPos++;
                $_SESSION['PostNumber'] = 0;
            }

            // are we done with the experiment? if so, send to finalQuestions.php
            if ($exitIfDone) {
                $item = $_SESSION['Trials'][$currentPos]['Procedure']['Item'];
                if ($item == 'ExperimentFinished') {
                    $_SESSION['finishedTrials'] = TRUE;             // stops people from skipping to the end
                 // header("Location: FinalQuestions.php");
                 // exit;
                }
            }
            
            return $writtenArray;
            
        }
        
        // setting up easier to use and read aliases(shortcuts) of $_SESSION data
        $condition      =& $_SESSION['Condition'];
        
        $currentPos     =& $_SESSION['Position'];
        $currentPost    =& $_SESSION['PostNumber'];
        $currentTrial   =& $_SESSION['Trials'][$currentPos];
        
        $currentStimuli =  $currentTrial['Stimuli'];
        createAliases($currentStimuli);
        
        // this will also create aliases of any columns that apply to the current trial (filtering out "post X" prefixes when necessary)
        // currentProcedure becomes an array of all columns matched for this trial, using their original column names
        $currentProcedure = ExtractTrial($currentTrial['Procedure'], $currentPost);
        
        /*
        if (!isset($trialType))
        {
            $error = array(
                'Error*Missing_Trial_Type' => 'Post ' . $_SESSION['PostNumber']
            );
            recordTrial();
            header('Location: experiment.php');
            exit;
        }
        */
        
        $trialType = strtolower($trialType);
        
        if (!isset($item)) {
            $item = $currentTrial['Procedure']['Item'];
        }
        
        if ($currentPost < 1) {
            $prefix = '';
        } else {
            $prefix = 'Post' . ' '  . $currentPost . ' ';
        }
        
        if (isset($currentTrial['Procedure'][$prefix . 'Text'])) {
            $text =& $currentTrial['Procedure'][$prefix . 'Text'];
            $text =  str_ireplace(array('$cue', '$answer'), array($cue, $answer), $text);
        }
        
        // if there is another item coming up then set it as $nextTrail
        if (array_key_exists($currentPos+1, $_SESSION['Trials'])) {
            $nextTrial =& $_SESSION['Trials'][$currentPos+1];
        } else {
            $nextTrial = FALSE;
        }
        
        // variables I'll need and/or set in trialTiming() function
        $timingReported = strtolower(trim( $maxTime ));
        $formClass    = '';
        $maxTime      = '';
        if( !isset( $minTime ) ) {
            $minTime    = 'not set';
        }

        #### Presenting different trial types ####
        $expFiles  = $up.$expFiles;                            // setting relative path to experiments folder for trials launched from this page
        $postTo    = 'experiment.php';
        $trialFail = FALSE;                                    // this will be used to show diagnostic information when a specific trial isn't working
        $trialFile = $_SESSION['Trial Types'][ $trialType ]['trial'];
        
        
        $title = 'Experiment';
        $_dataController = 'experiment';
        $_dataAction = $trialType;
        
        $keyMod = '';
        $findingKeys = FALSE;
        
        ####
        
        
        
        include $_SESSION['Trial Types'][$trialType]['scoring'];
        if (!isset($data)) { $data = $_POST; }
        $currentTrial['Response'] = placeData($data, $currentTrial['Response'], $keyMod);
        
        ?>
        <style>
            table                       {   margin: auto;       }
            table td                    {   padding: 10px 8px;  vertical-align: middle;  }
            table td:first-child        {   text-align: right;  }
            table td:not(:first-child)  {   text-align: left;   }
        </style>
        <h2 class="textcenter">Responses</h2>
        <table>
            <?php
            foreach ($currentTrial['Response'] as $name => $value) {
                ?><tr><td><?= htmlspecialchars($name) ?>:</td><td><?= htmlspecialchars($value) ?></td></tr><?php
            }
            ?>
        </table>
        <h2 class="textcenter">Stimuli</h2>
        <table>
            <?php
            foreach ($currentTrial['Stimuli'] as $name => $value) {
                ?><tr><td><?= htmlspecialchars($name) ?>:</td><td><?= htmlspecialchars($value) ?></td></tr><?php
            }
            ?>
        </table>
        <h2 class="textcenter">Procedure</h2>
        <table>
            <?php
            foreach ($currentTrial['Procedure'] as $name => $value) {
                ?><tr><td><?= htmlspecialchars($name) ?>:</td><td><?= htmlspecialchars($value) ?></td></tr><?php
            }
            ?>
        </table>
        <?php
        
        include 'Footer.php';
        
    }
