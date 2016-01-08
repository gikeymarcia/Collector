<?php
    // this function scans a directory and returns a list of csvs found inside
    function getCsvsInDir($dir) {
        $scan = scandir($dir);
        $output = array();
        foreach ($scan as $entry) {
            // get the lowercase last 4 characters of the file name,
            // and see if the file ends in ".csv"
            if (strtolower(substr($entry, -4)) === '.csv') {
                // if it matches, take everything up to the extension
                // e.g., from "myStimuli.csv", get "myStimuli"
                $output[] = $entry;
            }
        }
        return $output;
    }
    
    // this function creates a <select> element
    // $options is an array of options to fill the <select>
    // $class is used to construct the class name of the <select>
    // $column is used to offer the option for a new option, such as "New Stimuli"
    // $selected is the option that should be selected by default
    function createSelect($options, $newOptions, $class, $column, $selected) {
        if (substr($selected, -4) === '.csv') {
            $selected = substr($selected, 0, -4);
        }
        $select = "<select class='select$class'>";
        $select .= '<optgroup label="Existing">';
        foreach ($options as $opt) {
            if (substr($opt, -4) === '.csv') {
                $opt = substr($opt, 0, -4);
            }
            $select .= '<option';
            if ($opt === $selected) {
                $select .= ' selected';
            }
            $select .= ">$opt</option>";
        }
        $select .= '</optgroup><optgroup label="New">';
        foreach ($newOptions as $opt) {
            if (substr($opt, -4) === '.csv') {
                $opt = substr($opt, 0, -4);
            }
            $select .= '<option';
            if ($opt === $selected) {
                $select .= ' selected';
            }
            $select .= ">$opt</option>";
        }
        $select .= "<option class='newOption'>New $column</option>";
        $select .= '</optgroup>';
        
        return $select;
    }
    
    
    // start the session, load our custom functions, and create $_PATH
    require '../Code/initiateCollector.php';
    
    // if somehow this page was directly accessed, the POST variable wont be set
    if (isset($_POST['studyName'])) {
        // now lets get a list of possible experiments to edit
        $experiments = getCollectorExperiments();
        
        // if $studyName is not in the possible experiments, something went wrong
        // they probably tried to directly access this page, without submitting
        // a POST request
        // so, sent them back to index
        if (!in_array($_POST['studyName'], $experiments)) {
            $indexFile = __DIR__ . '/index.php';
            header('Location: ' . $indexFile);
            exit; // exit to prevent any more script execution
        } else {
            // all files should be taken from within the $studyName folder
            $_PATH->setDefault('Current Experiment', $_POST['studyName']);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
    
    if ($_PATH->getDefault('Current Experiment') === null) {
        $indexFile = __DIR__ . '/index.php';
        header('Location: ' . $indexFile);
        exit; // exit to prevent any more script execution
    }
    
    $title = 'Edit Conditions';
    require $_PATH->get('Header'); // include the header file, so that all of our css and jquery is enabled
    
    $thisFile = file_get_contents(__FILE__);
    echo '<textarea class="fileCode" readonly '
       . 'style="width: 15px; font-family: \'Courier New\'; '
       . 'font-size: 11pt; z-index: 1000; '
       . 'position: absolute; top: 5px; left: 5px;">'
       . htmlspecialchars($thisFile, ENT_QUOTES) . '</textarea>';
?>
<style>
    .newOption { color: #666; font-style: italic; }
    
    table { margin: 30px 0 60px; }
    
    td, th {
        border: 1px solid #666;
    }
    
    td { padding: 0; }
    th { padding: 2px 4px; }
    
    tr > td:first-child { text-align: right; padding-right: 12px; }
    
    td, th { background-color: #ddd; }
    
    table select {
        border-width: 0;
        background-color: #eee;
        width: 100%;
        height: 100%;
    }
    
    table input {
        border-width: 0;
        padding: 1px 2px;
    }
    
    table select:hover:not(:focus) {
        outline: 1px solid #3E9DFF;
        background-color: #ddf; 
    }
    
    table input:hover:not(:focus) {
        outline: 1px solid #3E9DFF;
        background-color: #eef; 
    }
    
    table select:focus,
    table input:focus {
        outline: 2px solid #3E9DFF;
    }
    
    table select:focus {
        background-color: #ccf;
    }
    
    table input:focus {
        background-color: #ddf;
    }
    
    table *::-moz-selection{
        background-color: #BBF;
    }
    table *::selection {
        background-color: #BBF;
    }
    
    .addBtnTd {
        position: relative;
        border: 0;
    }
    
    .addBtnTd button {
        position: absolute;
        left: 0px;
        top: 2px;
        white-space: nowrap;
    }
    
    .delBtnTd {
        border: 0;
        position: relative;
    }
    
    .delBtnTd button {
        position: absolute;
        top: 2px;
        left: 4px;
        padding: 0;
        line-height: 1;
        background-color: #f0f0f0;
        border-radius: 4px;
        color: #555;
    }
    
    .inputError {
        outline: 0px solid red;
        background-color: #FDD;
    }
    
    .changeExp > * {
        vertical-align: middle;
    }
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
?>
<h2>Creating the conditions for experiment: <?= $currentExp ?></h2>
<div class="changeExp">Change experiment: <?= $select ?> <button type="button" id="expButton">Change</button></div>
<?php
        
    $conditionsFile = $_PATH->get('Conditions');
    $conditionsData = getFromFile($conditionsFile, false); // load the conditions file into an array
    
    // prepare conditions file data for output
    foreach ($conditionsData as &$row) {
        foreach ($row as &$field) {
            $field = htmlspecialchars($field, ENT_QUOTES); // so that <p> tags inside the text wont screw up the table
        }
    }
    unset($row, $field);
    
    $headers = array_keys($conditionsData[0]); // get the array keys of the first row
    foreach ($headers as &$header) {
        $header = htmlspecialchars($header, ENT_QUOTES); // make sure there are no html elements in the column names
    }
    unset($header);
    
    
    
    $stimDir = $_PATH->get('Stimuli Dir');
    $stimuli = getCsvsInDir($stimDir); // now $stimuli is a list of stim files found in the Stimuli/ folder
    $newStim = array();
    foreach ($conditionsData as $row) {
        if (!in_array($row['Stimuli 1'], $stimuli)) {
            $newStim[] = $row['Stimuli 1'];
        }
    }
    
    $procDir    = $_PATH->get('Procedure Dir');
    $procedures = getCsvsInDir($procDir); // and $procedures is a list of proc csv files found in Procedure/
    $newProc = array();
    foreach ($conditionsData as $row) {
        if (!in_array($row['Procedure 1'], $procedures)) {
            $newProc[] = $row['Procedure 1'];
        }
    }
    
    
    
    echo '<table id="conditionsTable">'
       .     '<thead>'
       .         '<tr>';
       
    foreach ($headers as $header) {
        echo         '<th>' . $header . '</th>';
    }
    
    echo         '</tr>'
       .     '</thead>'
       .     '<tbody>';
       
    foreach ($conditionsData as $row) {
        echo     '<tr>';
        
        foreach ($row as $column => $field) {
            echo     '<td>';
            if ($column === 'Number') {
                echo $field;
            } elseif ($column === 'Stimuli') {
                echo createSelect($stimuli, $newStim, 'Stim', 'Stimuli', $field);
            } elseif ($column === 'Procedure') {
                echo createSelect($procedures, $newProc, 'Proc', 'Procedure', $field);
            } else {
                echo "<input value='$field'>";
            }
            echo     '</td>';
        }
        
        echo     '</tr>';
    }
    
    echo     '</tbody>'
       . '</table>';
    
    echo '<button type="button" id="saveButton">Save Changes</button>';
    
?>
<script>
    $("#expButton").on("click", function() {
        var cond = $("#experimentSelect").val();
        
        if (cond !== '') {
            var newCondForm = $("<form>");
            newCondForm.attr("method", "post");
            newCondForm.css("display", "none");
            
            newCondForm.append("<input name='studyName' value='" + cond + "'>");
            
            $("body").append(newCondForm);
            newCondForm.submit();
            
        }
    });

    $("option:selected").addClass("selected");
    
    $("select").on("change", function() {
        console.dir("changed");
        var selected = $(this).find(":selected");
        
        if (selected.hasClass("newOption")) {
            var promptMsg = selected.val();
            var newOpt = prompt("Create " + promptMsg + ":");
            if (newOpt === null || newOpt === '') {
                $(this).find(".selected").prop("selected", true);
            } else {
                $(this).find(".selected").removeClass("selected");
                var alreadyExists = false;
                var newOptLower = newOpt.toLowerCase();
                $(this).find("option").each(function() {
                    if ($(this).val().toLowerCase() === newOptLower) {
                        alreadyExists = true;
                        $(this).prop("selected", true).addClass("selected");
                        return false; // stop the each() loop
                    }
                });
                if (!alreadyExists) {
                    // huzzah! they've added a new option
                    var option = $("<option>");
                    option.append(newOpt);
                    
                    var selectClass = $(this).attr("class");
                    $("." + selectClass + " optgroup:last-child option:last-child")
                        .before(option);
                    var newSelected = selected.prev();
                    newSelected.prop("selected", true);
                    newSelected.addClass("selected");
                }
            }
        } else {
            $(this).find(".selected").removeClass("selected");
            selected.addClass("selected");
        }
    });
    
    
    
    function updateNumbers() {
        var i = false;
        $("table").find("th").each(function(index) {
            if ($(this).html() === "Number") {
                i = index;
                return false;
            }
        });
        if (i !== false) {
            ++i;
            var num = 1;
            $("tbody tr td:nth-child(" + i + ")").each(function() {
                if ($(this).find("button").length > 0) return true;
                $(this).html(num);
                ++num;
            });
        }
    }
    
    
    
    $("tbody tr").append("<td class='delBtnTd'><button type='button'>Delete</button></td>");
    
    $(".delBtnTd button").on("click", function() {
        if ($(this).closest("tbody").find("tr").length < 3) return;
        $(this).closest("tr").remove();
        updateNumbers();
    });
    
    
    
    $("table tbody").append("<tr><td class='addBtnTd'><button type='button'>Add New Condition</button></td></tr>");
    
    $(".addBtnTd button").on("click", function() {
        var tbody = $(this).closest("tbody");
        var copyRow = $(this).closest("tr").prev().clone(true);
        copyRow.find(".selected").removeClass("selected");
        copyRow.find("select").each(function() {
            $(this).find("option").first().addClass("selected").prop("selected", true);
        });
        copyRow.find("input").val("");
        $(this).closest("tr").before(copyRow);
        updateNumbers();
    });
    
    $("#saveButton").on("click", function() {
        var inputsEmpty = $([]);
        $("table input").each(function() {
            if ($(this).val() === '') {
                inputsEmpty = inputsEmpty.add($(this));
            }
        });
        
        if (inputsEmpty.length > 0) {
            inputsEmpty.addClass("inputError")
                .animate({outlineWidth: "2px"}, 500)
                .delay(1000)
                .animate({outlineWidth: "0"}, 500, function() {
                    $(this).removeClass("inputError");
                    $(this).removeAttr("style");
                });
        } else {
            var data = [];
            var headers = [];
            $("thead th").each(function() {
                headers.push($(this).html());
            });
            data.push(headers);
            
            $("tbody tr:not(:last-child)").each(function() {
                var temp = [];
                $(this).find("td:not(:last-child)").each(function() {
                    var thisInput = $(this).find(":input");
                    if (thisInput.length > 0) {
                        temp.push(thisInput.val());
                    } else {
                        temp.push(this.innerHTML);
                    }
                });
                data.push(temp);
            });
            
            var jsonData = JSON.stringify(data);
            
            var submit = $("<form>");
            var formInput = $("<input>");
            submit.attr("method", "post");
            submit.attr("action", "editMenu.php");
            submit.css("display", "none");
            
            formInput.attr("name", "Conditions");
            formInput.val(jsonData);
            
            submit.append(formInput);
            
            $("body").append(submit);
            submit.submit();
        }
    });
</script>
<?php
    
    
    
    require $_PATH->get('Footer');
