<?php
    if (!isset($_GET['ready']) AND $_POST === array()) {
        echo '<div style="text-align: center;">Please select a trial type. . . .</div>';
        exit;
    }
    
    if (isset($_GET['ready'])) {
        
        include 'trial.php';
        
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
        
        
        #### Copied from trial.php, update as needed ####
        // setting up easier to use and read aliases(shortcuts) of $_SESSION data
        $condition      =& $_SESSION['Condition'];
        $currentPos     =& $_SESSION['Position'];
        $currentPost    =& $_SESSION['PostNumber'];
        $currentTrial   =& $_SESSION['Trials'][$currentPos];
            $cue        =& $currentTrial['Stimuli']['Cue'];
            $answer     =& $currentTrial['Stimuli']['Answer'];
        
        // this will also create aliases of any columns that apply to the current trial (filtering out "post X" prefixes when necessary)
        // currentProcedure becomes an array of all columns matched for this trial, using their original column names
        $currentProcedure = ExtractTrial( $currentTrial['Procedure'], $currentPost );
        $trialType = strtolower($trialType);
        if (!isset($item)) {
            $item = $currentTrial['Procedure']['Item'];
        }
        if ($currentPost < 1) {
            $prefix = '';
        } else {
            $prefix = 'Post' . ' ' . $currentPost . ' ';
        }
        $text =& $currentTrial['Procedure'][$prefix . 'Text'];
        $text =  str_ireplace(array('$cue', '$answer'), array($cue, $answer), $text);
        
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
