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
  

  
        // Anthony's data analysis //

        $json_columns = json_encode($columns);
        $stats_options = [];
        
        if ($handle = opendir('Stats')) { //found on http://stackoverflow.com/questions/6497833/php-opendir-to-list-folders-only by Jason McCreary
          $blacklist = array('.', '..', 'somedir', 'somefile.php');
          while (false !== ($file = readdir($handle))) {
              if (!in_array($file, $blacklist)) {
                  array_push($stats_options,$file);
              }
          }
          closedir($handle);
        }
        $json_stats_options = json_encode($stats_options);

        ?>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        
  <script>

  var anthonyColumns  = <?= $json_columns ?>;
  var stats_options   = <?= $json_stats_options ?>;
  
  anthony_object = {
    // creating to populate with following script //
  }
  for(i =0; i<anthonyColumns.length; i++){
    anthony_object[anthonyColumns[i]]=[];
  }
  for(i = 0; i<row_array.length; i++){
    for (var key in anthony_object){
      anthony_object[key][i]=row_array[i][key]
    }    
  }
  

</script>

<link rel="stylesheet" type="text/css" href="GetDataStyle.css">
  <h1> Online Analyses </h1>
  <h2> Using javascript from jStat (https://github.com/jstat/jstat) </h2>
  
  <script src = "//cdn.jsdelivr.net/jstat/latest/jstat.min.js" ></script>
  
<span> 
    <input class="interface_button" type="button" id="gui_interface_button"       value="GUI">
    <input class="interface_button" type="button" id="script_interface_button"    value="Script">
    <input class="interface_button" type="button" id="custom_interface_button"    value="Custom">
</span> 
<div id="AnthonyInterface">
   
  <div class="interface" id="GUI_interface">
    <h3>GUI</h3>

      <span>
      <input class="GUI_type_button" type="button"    value="Descriptives">
      <input class="GUI_type_button" type="button"    value="T-Tests">
      <input class="GUI_type_button" type="button"    value="Regression">
      <input class="GUI_type_button" type="button"    value="Frequencies">
      <input class="GUI_type_button" type="button"    value="Other">
    </span>  

    <div id="Descriptives"  class="GUI_type">
    descriptives: <br>
    <select id="descriptive_variable">
      <option>select variable</option>
    </select>
    - update this to allow selection of multiple variables -
    <table id="descriptive_table"></table>
    
    
    <script>

    function clean_array (input_array){
      for(var i=0;i<input_array.length;i++){
        input_array[i]  = parseInt(input_array[i]);
      }
      return input_array;

    }

    
    // populate descriptive list
    
      var x = document.getElementById('descriptive_variable');
      for(i=0; i<anthonyColumns.length; i++){
        var option = document.createElement("option");
        option.text = anthonyColumns[i];
        x.add(option);    
      }    
      
      descriptive_attributes=["Number of responses","Empty responses","Mean","Stdev","SE","Maximum value","Minimum value"];
      
      descriptive_attributes_calculations=["no_present_in_array","no_empty_in_array","mean","sd","se","max","min"];
      
      for(i=0;i<descriptive_attributes.length; i++){
      
        var stats_scrpt = document.createElement('script');    
        stats_scrpt.src='Stats/'+descriptive_attributes_calculations[i]+'/stats.js';
        document.head.appendChild(stats_scrpt);
        
        
      }

      
      $("#descriptive_variable").change(function(){
              
        anthony_object[descriptive_variable.value] = clean_array(anthony_object[descriptive_variable.value]);      
              
        // Find a <table> element with id="myTable":
        var table = document.getElementById("descriptive_table");

        // clear table - not done
        

        // Create a header row
        var row = table.insertRow(0);
        
        cell0           = row.insertCell(0);
        cell0.innerHTML = "<b>Variable Name</b>";
        
        cell1           = row.insertCell(1);
        cell1.innerHTML = "<b>"+descriptive_variable.value+"</b>";
       

        for(i=0;i<descriptive_attributes.length; i++){
        
        
          var row = table.insertRow(i+1);
          this["cell"+0] = row.insertCell(0);
          this["cell"+0].innerHTML = descriptive_attributes[i];
          

          var function_name     = "calculate_"+descriptive_attributes_calculations[i];
          console.dir(window[function_name]);
          output_from_test  = window[function_name](anthony_object[descriptive_variable.value]);

          this["cell"+1] = row.insertCell(1);
          this["cell"+1].innerHTML = output_from_test; 

        }
      });
    
    </script>
    
    </div>
    <div id="T-Tests"       class="GUI_type">
      <select id="t_test_selection">
        <option value="select">Select an option</option>
        <option value="independent">Independent Samples</option>
        <option value="paired">Paired Samples</option>
        <option value="one_sample">One-Sample</option>
      </select>
    </div>
    
    <div class="t_test_div" id="t_test_independent"><?php require ("Stats/t_test_independent/display.html"); ?></div>
    <div class="t_test_div" id="t_test_paired"><?php require ("Stats/t_test_paired/display.html"); ?></div>
    <div class="t_test_div" id="t_test_one_sample"><?php require ("Stats/t_test_one_sample/display.html"); ?></div>
    
    
    <script>
      //call js files for each t-test
      
      var t_tests=['t_test_independent','t_test_paired','t_test_one_sample'];
      
      for(i=0;i<t_tests.length;i++){
        var display_scrpt = document.createElement('script');    
        display_scrpt.src='Stats/'+t_tests[i]+'/display.js';
        document.head.appendChild(display_scrpt);

        var stats_scrpt = document.createElement('script');    
        stats_scrpt.src='Stats/'+t_tests[i]+'/stats.js';
        document.head.appendChild(stats_scrpt);        
      }
      

      
      
      $(".t_test_div").hide();
      $("#t_test_selection").change(function(){
        alert($("#t_test_selection").val());
        $(".t_test_div").hide();
        $("#t_test_"+$("#t_test_selection").val()).show();
      });
    </script>
    
    <div id="Regression"    class="GUI_type">
    regression
    </div>
    <div id="Frequencies"   class="GUI_type">
    frequencies
    </div>
    <div id="Other"         class="GUI_type">
    sheet_management
    </div>

  </div>
  <div class="interface" id="Script_interface">
    <h3>Script</h3>
  </div>
  <div class="interface" id="Custom_interface">
    <h3>Custom</h3>

    <input id="stats_select" type="text" placeholder="what stats test do you want to run?">
    <div id="ajax_stats">
      <!-- here's where display files will be presented !-->
    </div>


  </div>
  
  </div>

</div>

<script>
  $(".interface").hide();
  $(".interface_button").click(function(){
    $(".interface").hide();
    $("#"+this.value+"_interface").show();
  });
  
  $(".GUI_type").hide();
  $(".GUI_type_button").click(function(){
    $(".GUI_type").hide();
    $("#"+this.value).show();
  });
</script>

<br><br>

  <div id="outputArea">
  <h3> Output </h3>
  </div>
  <div> List of functions we want:
<br>Renaming variables   
<br>paired t-test
<br>helper bar on right   
<br>handle multiple sheets  
<br>allow users to add custom scripts. Needs HTML part and javascript component. 
<br>allow custom version of our analyses: and comparisons of whether the custom version is quicker. Test if they get the same results 1000 times. If so, then e-mail us to investigate implementing it   
<br>output has its own space and style
<br>allow users to use script to rerun analysis (so it's bidirectional)
<br>create new columns in data - that are presented on the screen
<br>a list of dataframes and variables
<br>allow languages to be used, e.g. R syntax?
</div>

<script>

var returned_script;

$("#stats_select").on('input',function(){ // ajax in Stats tool
  var stats_selected = $("#stats_select").val().toLowerCase();
  if(stats_options.indexOf(stats_selected) !=-1){
    //alert(stats_selected);
    $.get(
      'Stats/'+stats_selected+'/display.html',
      {
        first: 'first param',
        second: 'second param'
      },
      function(returned_data) {
        //console.dir(returned_data);
        $("#ajax_stats").html(returned_data);
        
      }
    );
    
    var display_scrpt = document.createElement('script');    
    display_scrpt.src='Stats/'+stats_selected+'/display.js';
    document.head.appendChild(display_scrpt);
    
    var stats_scrpt = document.createElement('script');    
    stats_scrpt.src='Stats/'+stats_selected+'/stats.js';
    document.head.appendChild(stats_scrpt);



  }
});
</script>
        
        <?php
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
