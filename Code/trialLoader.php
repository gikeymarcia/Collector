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
        
        
        
        function arrayToLineBroken ($row, $fileName, $d = NULL, $encodeUtf8ToWin = true) {
            return $row;
        }
        
        function recordTrial($extraData = array(), $exitIfDone = true, $advancePosition = true) {
            return null;
        }
        
        
        
        #### Copied from experiment.php, update as needed ####
        
        if (!isset($_SESSION['Timestamp'])) {
            $_SESSION['Timestamp'] = microtime(true);
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
        
        if (!isset($trialType)) {
            $error = array(
                'Error*Missing_Trial_Type' => 'Post ' . $_SESSION['PostNumber']
            );
            recordTrial();
            header('Location: experiment.php');
            exit;
        }
        
        $trialType = strtolower($trialType);
        
        $trialFiles = getTrialTypeFiles($trialType);
        if (isset($trialFiles['script'])) {
            $addedScripts = array($trialFiles['script']);
        }
        if (isset($trialFiles['style'])) {
            $addedStyles  = array($trialFiles['style']);
        }
        
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
            $nextTrial = false;
        }
        
        // variables I'll need and/or set in trialTiming() function
        $timingReported = strtolower($maxTime);         // get value from 'Max Time' column
        $formClass = '';
        $maxTime   = '';
        if (!isset($minTime)) {
            $minTime = 'not set';
        }
        
        
        ob_start();
        
        #### Presenting different trial types ####
        $postTo    = 'experiment.php';
        $trialFail = false;                                    // this will be used to show diagnostic information when a specific trial isn't working
        
        $title = 'Experiment';
        $_dataController = 'experiment';
        $_dataAction = $trialType;
        
        if (isset($trialFiles['helper'])) include $trialFiles['helper'];
        
        ####
        
        $keyMod = '';
        
        include $trialFiles['scoring'];
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
