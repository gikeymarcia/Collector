<?php
    session_start();
    
    #### turns a string into an array, converting something like '2,4::6' into array(2, 4, 5, 6)
    function rangeToArrayTEMP ($string, $seperator = ',', $connector = '::') {
        $output = array();
        $ranges = explode($seperator, $string);
        foreach ($ranges as $range) {
            $endPoints = explode($connector, $range);
            $count = count($endPoints);
            if ($count === 1) {
                $output[] = trim($endPoints[0]);
            } else {
                $output = array_merge($output, range(trim($endPoints[0]), trim($endPoints[$count-1])));
            }
        }
        return $output;
    }
    
    $gets = array();
    foreach ($_GET as $name => $val) {
        $name = explode('_', $name);
        $category = array_shift($name);
        $name = implode('_', $name);
        
        $gets[$category][$name] = $val;
    }
    
    if (!isset($gets['Procedure']['Trial_Type']) AND !isset($_SESSION['trialTester'])) {
        echo '<div style="text-align: center;">Please select a trial type. . . .</div>';
        exit;
    }
    
    if (isset($gets['Procedure']['Trial_Type'])) {
    
        #### Simulating login.php
    
        $_SESSION = array();
        $_SESSION['trialTester'] = TRUE;
        $_SESSION['Debug'] = FALSE;     // this just messes with timing
        
        $_SESSION['Trial Types'] = require 'scanTrialTypes.php';
        
        $_SESSION['Username']   = 'TrialTester';
        $_SESSION['ID']         = 'TrialTester';
        $_SESSION['Position']   = 1;
        $_SESSION['PostNumber'] = 0;
        $_SESSION['Condition']  = array(
            'Number'                => 1,
            'Stimuli'               => 'test',
            'Procedure'             => 'test',
            'Condition Description' => 'testing trial types',
        );
        
        foreach ($gets as $category => $var) {
            $_SESSION[$category] = array(0 => 0, 1 => 0);
            foreach ($var as $column => $val) {
                $column = strtr($column, '_', ' ');
                $values = explode('|', $val);
                foreach ($values as $i => $v) {
                    $_SESSION[$category][$i+2][$column] = $v;
                }
            }
        }
        
        $defaultItems = array();
        $stimCount = count($_SESSION['Stimuli']);
        for ($i=2; $i<$stimCount; ++$i) {
            $defaultItems[] = $i;
        }
        $defaultItems = implode(',', $defaultItems);
        
        $defaults = array(
            'Stimuli'   => array(
                'Cue'        => '', 
                'Answer'     => '', 
                'Shuffle'    => 'off'
            ),
            'Procedure' => array(
                'Item'       => $defaultItems, 
                'Trial Type' => '', 
                'Timing'     => 'user', 
                'Text'       => '', 
                'Shuffle'    => 'off'
            )
        );
        foreach ($defaults as $category => $columns) {
            foreach ($_SESSION[$category] as $i => &$row) {
                if ($row === 0) { continue; }
                foreach ($columns as $column => $default) {
                    if (!isset($row[$column])) {
                        $row[$column] = $default;
                    }
                }
            }
        }
        unset($row);
        
        
        $_SESSION['Trials'] = array();
        $i = 0;
        foreach($_SESSION['Procedure'] as $procRow) {
            if ($procRow === 0) { continue; }
            ++$i;
            $items = rangeToArrayTEMP($procRow['Item']);
            $stim = array();
            foreach ($items as $item) {
                if (isset($_SESSION['Stimuli'][$item])) {
                    foreach ($_SESSION['Stimuli'][$item] as $column => $value) {
                        $stim[$column][] = $value;
                    }
                }
            }
            if ($stim === array()) {
                foreach ($_SESSION['Stimuli'][2] as $column => $unused) {
                    $stim[$column] = 'n/a';
                }
            } else {
                foreach ($stim as &$values) {
                    $values = implode('|', $values);
                }
                unset($values);
            }
            $_SESSION['Trials'][$i]['Stimuli']   = $stim;
            $_SESSION['Trials'][$i]['Procedure'] = $procRow;
            $_SESSION['Trials'][$i]['Response']  = array();
        }
        
        
        #### Finished pseudo-login
        
    
        session_write_close();
        
    
        include 'trial.php';
        
        ?>
        <script>
            $(window).load(function(){
                $("form").attr("action", "trialLoader.php");
            });
        </script>
        <?php
        
    } else {
    
        session_write_close();
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
