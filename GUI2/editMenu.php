<?php
    // start the session, load our custom functions, and create $_PATH
    require '../Code/initiateCollector.php';
    
    if (($currentExp = $_PATH->getDefault('Current Experiment')) === null) {
        $indexFile = __DIR__ . '/index.php';
        header('Location: ' . $indexFile);
        exit; // exit to prevent any more script execution
    } elseif (!is_file($_PATH->get('Conditions'))) {
        $indexFile = __DIR__ . '/index.php';
        header('Location: ' . $indexFile);
        exit; // exit to prevent any more script execution
    }
    
    
    
    if (isset($_POST['Conditions'])) {
        if ($_PATH->getDefault('Current Experiment') === null) {
            exit('An error has occured. No data was stored.');
        }
        
        // convert the posted json-encoded string into an array
        $conditionsData = json_decode($_POST['Conditions'], true);
        
        if (!is_array($conditionsData)) {
            exit('An error has occured. No data was stored.');
        }
        
        if (count($conditionsData) < 2) {
            exit('An error has occured. No data was stored.');
        }
        
        foreach ($conditionsData as $row) {
            if (!is_array($row)) {
                exit('An error has occured. No data was stored.');
            }
            
            if (!isset($count)) {
                $count = count($row);
            } else {
                if (count($row) !== $count) {
                    exit('An error has occured. No data was stored.');
                }
            }
            
            foreach ($row as $field) {
                if ((string) $field === '') {
                    exit('An error has occured. No data was stored.');
                }
            }
        }
        
        
        
        $headers = array_shift($conditionsData);
        
        $cleanData = array();
        
        foreach ($conditionsData as $row) {
            $cleanData[] = array_combine($headers, $row);
        }
        
        foreach ($cleanData as &$row) {
            $row['Stimuli']   = $row['Stimuli']   . '.csv';
            $row['Procedure'] = $row['Procedure'] . '.csv';
        }
        unset($row);
        
        
        
        $conditionsFile = $_PATH->get('Conditions');
        
        $conditionsHandle = fopen($conditionsFile, 'w');
        if (!$conditionsHandle) exit('Error: conditions file not readable: it may be open by another program');
        
        fputcsv($conditionsHandle, $headers);
        
        foreach ($cleanData as $row) {
            fputcsv($conditionsHandle, $row);
        }
        
        fclose($conditionsHandle);
        
        
        // should have successfully stored the data by this point
        // refresh to get rid of post variable
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    
    
    if (isset($_POST['targetCondition'])) {
        $experiments = getCollectorExperiments();
        if (!in_array($_POST['targetCondition'], $experiments)) {
            exit('Error: condition not found');
        }
        $_PATH->setDefault('Current Experiment', $_POST['targetCondition']);
        // refresh to get rid of post variable
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    
    
    $title = 'Edit Menu';
    require $_PATH->get('Header');
    
    $thisFile = file_get_contents(__FILE__);
    echo '<textarea class="fileCode" readonly '
       . 'style="width: 15px; font-family: \'Courier New\'; '
       . 'font-size: 11pt; z-index: 1000; '
       . 'position: absolute; top: 5px; left: 5px;">'
       . htmlspecialchars($thisFile, ENT_QUOTES) . '</textarea>';
?>
<style>
    body { display: block; text-align: center; }
    table { margin: 10px auto 30px; }
</style>
<?php
    
    
    
    $experiments = getCollectorExperiments();
    $currentExp  = $_PATH->getDefault('Current Experiment');
    
    $select = '<select id="experimentSelect">';
    foreach ($experiments as $exp) {
        $select .= '<option';
        if ($exp === $currentExp) {
            $select .= ' selected value=""';
        }
        $select .= '>' . $exp . '</option>';
    }
    $select .= '</select>';
    
    echo '<h2>Editing Experiment: ' . $select . '</h2>';
?>
<script>
    $("#experimentSelect").on("change", function() {
        if ($(this).val() !== '') {
            var form = $("<form>");
            form.attr("method", "post");
            form.css("display", "none");
            form.append("<input name='targetCondition' value='" + $(this).val() + "'>");
            $("body").append(form);
            form.submit();
        }
    });
</script>
<?php


    
    echo '<div>Here are the conditions for this experiment.
               To edit them, <a href="editConditions.php">click here</a>
          </div>';
    $conditionsFile = $_PATH->get('Conditions');
    
    $storedData = getFromFile($conditionsFile, false);
    display2dArray($storedData);
    
    
    
    $stimFiles = array();
    
    $stimDir = $_PATH->get('Stimuli Dir');
    foreach ($storedData as $row) {
        $stimFile = $row['Stimuli'];
        $filePath = $stimDir . '/' . $stimFile;
        if (fileExists($filePath)) {
            $stimFiles[$stimFile] = true;
        } else {
            $stimFiles[$stimFile] = false;
        }
    }
    
    $procFiles = array();
    
    $procDir = $_PATH->get('Procedure Dir');
    foreach ($storedData as $row) {
        $procFile = $row['Procedure'];
        $filePath = $procDir . '/' . $procFile;
        if (fileExists($filePath)) {
            $procFiles[$procFile] = true;
        } else {
            $procFiles[$procFile] = false;
        }
    }
    
?>
<style>
    .expFilesTable td {
        padding: 2px 4px;
        text-align: left;
    }
    
    .tableNote {
        color: #666;
        font-style: italic;
    }
</style>
<?php

    echo 'Stimuli Files (click to edit):<br>';
    echo '<table class="expFilesTable">';
    foreach ($stimFiles as $file => $exists) {
        echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?expFile=' . urlencode($file)
           . '">' . $file . '</a></td><td class="tableNote">';
        if (!$exists) echo '(does not exist yet)';
        echo '</td></tr>';
    }
    echo '</table>';

    echo 'Prcoedure Files (click to edit):<br>';
    echo '<table class="expFilesTable">';
    foreach ($procFiles as $file => $exists) {
        echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?expFile=' . urlencode($file)
           . '">' . $file . '</a></td><td class="tableNote">';
        if (!$exists) echo '(does not exist yet)';
        echo '</td></tr>';
    }
    echo '</table>';
