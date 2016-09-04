<?php
    require '../../initiateTool.php';
    require 'getdataFunctions.php';
    
    $filePrefixes = array(
        'Output' => 'Exp_',
        'Side'   => 'Side_',
        'Status_Begin' => 'Status_Begin_',
        'Status_End' => 'Status_End_'
    );
    
    if ($_POST !== array()) {
        require 'getdataGenerate.php';
        exit;
    }
    
    echo '<form id="GetDataForm" method="POST" target="_self">';
    
    require 'scan.php';
    
    $dataScan = getDataScan($_PATH);
    
    $trialTypes = array_keys(getAllTrialTypeFiles());
    
    $massChecker2 = '<input type="checkbox" class="massCheckbox" data-nest="2">';
    $massChecker3 = '<input type="checkbox" class="massCheckbox" data-nest="3">';
    
    $expander = '<button type="button" class="expander">&#11014;</button>';
    $expanderCont = '<span class="expanderContainer">' . $expander . '</span>';
?>

<link rel="stylesheet" type="text/css" href="GetDataStyle.css">

<div class="textcenter"><button id="GetDataDownloadButton" type="submit">Click here to download your data</button></div>

<div class="blockRow"><div class="blockContainer">
    <h1>Main Settings <?= $expander ?></h1>
    <div>
        <div class="questionField">
            <h3>What format would you like your data in?</h3>
            <div class="radioTable">
                <label> <span><input type="radio" name="format" value="csv" checked></span> <span>CSV: comma-separated spreadsheet</span> </label>
                <label> <span><input type="radio" name="format" value="txt"></span> <span>TXT: tab-delimited text file</span> </label>
                <label> <span><input type="radio" name="format" value="html"></span> <span>HTML: see a table in your browser (good for previewing data)</span> </label>
                <label> <span><input type="radio" name="format" value="summary"></span> <span>Summary Statistics</span> </label>
                <label> <span><input type="radio" name="format" value="stats"></span> <span>Statistics</span> </label>
            </div>
        </div>

        <div class="questionField">
            <h3><label class="massLabel"><?= $massChecker3 ?> Which data files do you want to select?</label></h3>
            <div class="radioTable">
                <label> <span><input type="checkbox" name="files[]" value="exp"  checked></span> <span>Main Experiment Output</span> </label>
                <label> <span><input type="checkbox" name="files[]" value="side" checked></span> <span>Side Data</span> </label>
                <label> <span><input type="checkbox" name="files[]" value="beg"  checked></span> <span>Status Begin</span> </label>
                <label> <span><input type="checkbox" name="files[]" value="end"  checked></span> <span>Status End</span> </label>
            </div>
        </div>
    </div>
</div></div>

<div class="blockRow">
    <div id="RowFilters" class="blockContainer">
        <h1>Row Filters <?= $expander ?></h1>
        <div class="inputBlock">
            <h3><label class="massLabel"><?= $massChecker3 ?> Trial Types</label> <?= $expander ?></h3>
            <div class="radioTable">
                <?php
                    foreach ($trialTypes as $type) {
                        $checkbox = "<input type='checkbox' name='trialTypes[]' value='$type' checked>";
                        echo "<label> <span>$checkbox</span> <span>$type</span> </label>\r\n";
                    }
                ?>
            </div>
        </div>
    </div>

    <div id="ColumnFilters" class="blockContainer">
        <h1>Column Filters <?= $expander ?></h1>
        <div>
            <?php
                $fileLabels = array(
                    'Output' => 'Experiment Output',
                    'Side'   => 'Side Data',
                    'Status_Begin' => 'Status Begin',
                    'Status_End' => 'Status End'
                );
                
                foreach ($dataScan['Columns'] as $file => $columns) {
                    echo '<div class="inputBlock">';
                    echo '<h3><label class="massLabel">' . $massChecker3 . $fileLabels[$file] . "</label> $expander</h3>";
                    echo '<div class="radioTable">';
                    foreach ($columns as $col) {
                        $col = htmlspecialchars($col, ENT_QUOTES);
                        $pre = $filePrefixes[$file];
                        $name = $file . '_cols[]';
                        $checkbox = "<input type='checkbox' name='$name' value='$pre$col' checked>";
                        echo "<label> <span>$checkbox</span> <span>$col</span> </label>\r\n";
                    }
                    echo '</div>';
                    echo '</div>';
                }
            ?>
        </div>
    </div>
</div>

<div id="Users" class="blockRow"><div class="blockContainer">
    <h1><label class="massLabel"><?= $massChecker3 ?>Users</label><?= $expanderCont ?></h1>
    <div>
    <?php
        foreach ($dataScan['Experiments'] as $exp => $debugTypes) {
            echo '<div>';
            echo "<h2><label class='massLabel'>$massChecker3 $exp</label>$expanderCont</h2><div>";
            foreach ($debugTypes as $debugType => $flags) {
                echo "<div><h3><label class='massLabel'>$massChecker3 $debugType</label>$expanderCont</h3><div>";
                foreach ($flags as $flag => $conds) {
                    $checked = ($flag === 'Complete Data') ? 'checked' : '';
                    echo "<div><h4><label class='massLabel'>$massChecker3 $flag</label>$expanderCont</h4><div>";
                    foreach ($conds as $cond => $userInfo) {
                        echo "<div><h5><label class='massLabel'>$massChecker3 $cond</label>$expanderCont</h5><div class='radioTable'>";
                        foreach ($userInfo as $username => $ids) {
                            foreach ($ids as $id => $idData) {
                                $filename = $idData['Begin']['Output_File'];
                                $value = "$exp/$debugType/$username/$id/$filename";
                                $checkbox = "<input type='checkbox' name='u[]' value='$value' $checked>";
                                echo "<label> <span>$checkbox</span> <span>$username ($id)</span> </label>\r\n";
                            }
                        }
                        echo '</div></div>';
                    }
                    echo '</div></div>';
                }
                echo '</div></div>';
            }
            echo '</div>';
            echo '</div>';
        }
    ?>
    </div>
</div></div>

</form>

<script>
    $(window).load(function() {
        $(":focus").blur();
        
        var userTitles = $("#Users").find(".massLabel");
        var maxWidth = 0;
        
        userTitles.each(function() {
            maxWidth = Math.max(maxWidth, $(this).width()+0.5);
        });
        
        userTitles.width(maxWidth);
        
        $("body").css("min-height", $("body").height()+2+"px");
    });
    
    $("input[name='format']").on("change", function() {
        var format = $(this).val();
        if (format === 'html' || format === 'summary') {
            $("form").attr("target", "_blank");
        } else {
            $("form").attr("target", "_self");
        }
    });
    
    $(".massCheckbox").each(function() {
        var mass = $(this);
        var nestedLevel = parseInt(mass.data("nest")) || 1;
        var targets = mass;
        var targetMasses, targetIndividuals;
        
        for (var i=0; i<nestedLevel; ++i) {
            targets = targets.parent();
        }
        
        targets = targets.find("input[type='checkbox']");
        targetMasses      = targets.filter(".massCheckbox").not(this);
        targetIndividuals = targets.not(".massCheckbox");
        
        mass.on("change", function() {
            targets.prop("checked", mass.prop("checked"))
            targetMasses.prop("indeterminate", false);
        });
        
        targetIndividuals.on("change", function() {
            setMassCheckerState(mass, targetIndividuals);
        });
        
        targetMasses.on("change", function() {
            setTimeout(function() {
                setMassCheckerState(mass, targetIndividuals);
            }, 0);
        });
        
        targetIndividuals.first().change();
    });
    
    function setMassCheckerState(massChecker, targets) {
        var checked = targets.filter(":checked").length;
        var total   = targets.length;
        
        if (checked === 0) {
            massChecker.prop("checked", false);
            massChecker.prop("indeterminate", false);
        } else if (checked < total) {
            massChecker.prop("checked", false);
            massChecker.prop("indeterminate", true);
        } else {
            massChecker.prop("checked", true);
            massChecker.prop("indeterminate", false);
        }
    }
    
    $(".expander").on("click", function() {
        var title   = $(this).parent();
        
        if (title.hasClass("expanderContainer")) {
            title = title.parent();
        }
        
        var content = title.next();
        var timing  = 90;
        
        if (content.is(":visible")) {
            title.width(Math.max(
                content.width(), 
                title.width()+0.5 // correcting by half a pixel because something seems to be rounding down and causing format changes
            ));
            content.hide(timing);
            title.parent(".blockContainer").addClass("collapsed");
            $(this).html("&#11015;");
        } else {
            content.show(timing);
            setTimeout(function() {
                title.width("auto");
            }, timing+50);
            title.parent(".blockContainer").removeClass("collapsed");
            $(this).html("&#11014;");
        }
    });
    
    var eligibleLabels = $(".radioTable label:not('.massLabel')");
    var selectingLabels = false;
    var startingLabel = null;
    var intendedState = null;
    var selectionContainer = null;
    
    eligibleLabels.on("mousedown", function() {
        selectingLabels = true;
        startingLabel = this;
        var _this = $(this);
        _this.addClass("SelectedLabel");
        intendedState = !_this.find("input").prop("checked");
        selectionContainer = _this.closest(".radioTable")[0];
    })
    .on("mouseover", function() {
        if (selectingLabels &&
            (selectionContainer === $(this).closest(".radioTable")[0])
        ) $(this).addClass("SelectedLabel");
    })
    .on("mouseup", function() {
        if (this === startingLabel) $(this).removeClass("SelectedLabel"); // will already trigger a click
    });
    
    $("body").on("mouseup", function() {
        if (!selectingLabels) return;
        
        selectingLabels = false;
        
        var selected = $(".SelectedLabel");
        
        $(".SelectedLabel").removeClass("SelectedLabel")
            .find("input[type='checkbox']").prop("checked", intendedState)
                .first().change();
        
        startingLabel = null;
    });
</script>
