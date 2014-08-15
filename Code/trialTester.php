<?php
    require 'initiateCollector.php';
    
    $trialTypes = require 'scanTrialTypes.php';
    
    if (!isset($_SESSION['Trial Tester']) OR isset($_POST['resetSession'])) {
        $_SESSION = array();
        $_SESSION['Trial Tester'] = TRUE;
    } elseif (isset($_POST['Procedure_Trial_Type'])) {
    
        $redirect = TRUE;
        $posts = array();
        foreach ($_POST as $name => $val) {
            $name = explode('_', htmlspecialchars_decode($name));
            $category = array_shift($name);
            $name = implode('_', $name);
            
            if($name === 'new') { var_dump($val); }
            
            if ($category === 'Stimuli' OR $category === 'Procedure') {
                if (is_array($val)) {
                    foreach ($val as &$v) {
                        $v = trim(htmlspecialchars_decode($v));
                    }
                    unset($v);
                    $val = implode('|', $val);
                } else {
                    $val = trim(htmlspecialchars_decode($val));
                }
                $posts[$category][$name] = $val;
            }
        }
        
        $trialType = $posts['Procedure']['Trial_Type'];
        $trialType = strtolower($trialType);
        
        if (isset($trialTypes[$trialType])) {
        
            #### Simulating login.php
        
            $_SESSION = array();
            $_SESSION['Trial Tester'] = TRUE;
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
            
            foreach ($posts as $category => $var) {
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
                $items = rangeToArray($procRow['Item']);
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
            
        }
        
    }
    
    if (isset($redirect)) {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
    
    include 'Header.php';
    
    $trialTypeOptions = '';
    if (isset($_SESSION['Procedure'])) {
        foreach (array_keys($trialTypes) as $trialType) {
            if (strtolower(trim($_SESSION['Procedure'][2]['Trial Type'])) === $trialType) {
                $trialTypeOptions .= '<option selected>' . $trialType . '</option>';
            } else {
                $trialTypeOptions .= '<option>' . $trialType . '</option>';
            }
        }
    } else {
        $trialTypeOptions .= '<option hidden disabled selected></option>';
        $trialTypeOptions .= '<option>' . implode('</option><option>', array_keys($trialTypes)) . '</option>';
    }
    
    $th = '<th contenteditable="true">';
    $td = '<td contenteditable="true">';
    
    $stimTable = '<table class="expSettings" id="Stimuli">';
    if (isset($_SESSION['Stimuli'])) {
        $headers = $_SESSION['Stimuli'][2];
        unset($headers['Shuffle']);
        $headers = array_keys($headers);
        foreach ($headers as &$cell) {
            $cell = htmlspecialchars($cell);
        }
        $stimTable .= '<thead>' . '<tr>' . $th . implode('</th>' . $th, $headers) . '</th>' . '</tr>' . '</thead>';
        $stimTable .= '<tbody>';
        $stimCount = count($_SESSION['Stimuli']);
        for ($i=2; $i<$stimCount; ++$i) {
            $stimRow = $_SESSION['Stimuli'][$i];
            unset($stimRow['Shuffle']);
            foreach ($stimRow as &$cell) {
                $cell = htmlspecialchars($cell);
            }
            $stimTable .=         '<tr>' . $td . implode('</td>' . $td, $stimRow) . '</td>' . '</tr>';
        }
        unset($cell);
        $stimTable .= '</tbody>';
    } else {
        $stimTable .= '
                    <thead>
                        <tr>' . $th . 'Cue</th>' . $th . 'Answer</th></tr>
                    </thead>
                    <tbody>
                        <tr>' . $td .    '</td>' . $td .       '</td></tr>
                    </tbody>';
    }
    $stimTable .= '</table>';
    
    $procTable = '<table class="expSettings" id="Procedure">';
    if (isset($_SESSION['Procedure'])) {
        $loaderURL = 'trialLoader.php?ready=1';
        $proc = $_SESSION['Procedure'][2];
        unset($proc['Trial Type'], $proc['Shuffle']);
        $head = array_keys($proc);
        foreach ($proc as &$cell) {
            $cell = htmlspecialchars($cell);
        }
        foreach ($head as &$cell) {
            $cell = htmlspecialchars($cell);
        }
        unset($cell);
        $procTable .= '<thead>' . '<tr>' . $th . implode('</th>' . $th, $head) . '</th>' . '</tr>' . '</thead>';
        $procTable .= '<tbody>' . '<tr>' . $td . implode('</td>' . $td, $proc) . '</td>' . '</tr>' . '</tbody>';
    } else {
        $loaderURL = 'trialLoader.php';
        $procTable .= '
                    <thead>
                        <tr>' . $th . 'Text</th>' . $th . 'Settings</th></tr>
                    </thead>
                    <tbody>
                        <tr>' . $td .     '</td>' . $td .         '</td></tr>
                    </tbody>';
    }
    $procTable .= '</table>';
    
?>
<style>
    .cframe-inner   {   vertical-align: top;    height: 100%;    }

    iframe          {   border: 0px solid #000; width: 100%; border-top-width: 1px; min-height: 100%;  }
    .trialOption    {   text-align: center; margin: 15px;   display: inline-block;   }
    #allContain     {   height: 100%;   }
    
    .expFile        {   width: 49%; display: inline-block;   padding: 0px 100px 0 0; vertical-align: top;  }
    
    .tableContainer {   display: inline-block;   max-width: 100%;    overflow: auto;  }
    .blockContainer {   display: inline-block;  text-align: left;   max-width: 100%;    white-space: nowrap;  }
    .newColumnBtn   {   vertical-align: top;    }
    .expSettings    {   text-align: center; }
    .expSettings td,
    .expSettings th {   min-width: 100px;   border: 1px solid #ccc; vertical-align: middle; padding: 1px 5px;   }
    .expSettings th {   font-weight: bold;  }
    
    #settingsForm   {   text-align: center; }
</style>
<script>
    $(window).load(function(){
        $(".newColumnBtn").on("click", function(){
            var targetTable = $(this).closest(".blockContainer").find(".expSettings");
            $(targetTable).find("thead tr").append('<th contenteditable="true"><br></th>');
            $(targetTable).find("tbody tr").append('<td contenteditable="true"><br></td>');
        });
        $(".newRowBtn").on("click", function(){
            var targetTable = $(this).closest(".blockContainer").find(".expSettings");
            var columns = $(targetTable).find("th").length;
            var newContent = "<tr>";
            for( var i=0; i<columns; ++i ) {
                newContent += '<td contenteditable="true"><br></td>';
            }
            newContent += "</tr>";
            $(targetTable).find("tbody").append(newContent);
        });
        $("input[name='submitSettings']").on("click", function(){
            if ($("select[name='Procedure_Trial_Type']").val() === null) { return false; }
            $(".expSettings").each(function(){
                var rows, i, cols, j, name, content, category;
                var rows = $(this).find("tbody tr").length;
                var cols = $(this).find("thead th").length;
                category = $(this).attr("id");
                content = "";
                for (i=1; i<=cols; ++i) {
                    name = $(this).find("thead th:nth-child("+i+")").html().replace(/<br>/g, "").replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
                    for (j=1; j<=rows; ++j) {
                        content += '<input type="text" name="' + category + '_' + name + '[]" value="' + $(this).find("tbody tr:nth-child("+j+") td:nth-child("+i+")").html().replace(/<br>/g, "").replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") + '" />';
                    }
                }
                $("#fileData").append(content);
            });
            $("#settingsForm").submit();
        });
        
        $("input[name='reset']").on("click", function(){
            $("#fileData").append('<input type="text" name="resetSession" />');
            $("#settingsForm").submit();
        });
    });
</script>
<div id="allContain">
    <form id="settingsForm" method="POST" action="" >
        <h2>Welcome to the trial type tester!</h2>
        <div>Please choose the settings you want. <input type="button" name="submitSettings" value="Go!" /> <input type="button" name="reset" value="Reset" /> </div>
        <div>Trial Type: 
            <select name="Procedure_Trial_Type">
                <?= $trialTypeOptions ?>
            </select>
        </div>
        <div class="expFile">
            <h3>Stimuli File</h3>
            <div class="blockContainer">
                <div class="tableContainer">
                    <?= $stimTable ?>
                </div>
                <button type="button" class="newColumnBtn">New Column</button>
                <br>
                <button type="button" class="newRowBtn">New Row</button>
            </div>
        </div>
        <div class="expFile">
            <h3>Procedure File</h3>
            <div class="blockContainer">
                <div class="tableContainer">
                    <?= $procTable ?>
                </div>
                <button type="button" class="newColumnBtn">New Column</button>
                <br>
                <button type="button" class="newRowBtn" disabled="disabled">New Row</button>
            </div>
        </div>
        <div id="fileData" style="display: none;"></div>
    </form>
    <iframe src="<?= $loaderURL ?>"></iframe>
    <?php 
        
        include 'Footer.php';

    ?>
</div>
