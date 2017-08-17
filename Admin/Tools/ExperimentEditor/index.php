<?php
    require_once '../../initiateTool.php';
    
    //need to distinguish between when this is being run as an add on to simulator and an independent tool
    
    $current_address = $_SERVER['REQUEST_URI'];
    if(strpos($current_address,"Simulator") >0){
        $simulator_on_off="on";
    } else {
        // not in simulator, add LoadExperiment.php
        require("LoadExperiment.php");
        $simulator_on_off="off";
        //helper bar
        ?>
        <table id="support_bar"> 
            <tr>
                <td><?php require("../Simulator/SupportBars/tutorial.php");   ?></td>
                <td><?php require("../Simulator/SupportBars/interfaces.php"); ?></td>
                <td><?php require("../Simulator/SupportBars/HelperBar.php");  ?></td>
            </tr>
        </table>   
        <?php        
    }
?>

<style>
#cell_content{
    border: 1px solid black;
    color:blue;
}
.handsontable td{
    max-width:100px;
    text-align:left;
}
#cell_content_border{
    border: 2px solid white;
    padding: 1px;
    border-radius: 5px;
}

.handsontable tr > td.current {
    !background-color: rgb(0,0,360);
    !color:white;
}

</style>


<link rel="stylesheet" href="../handsontables/handsontables.full.css">
<script src="../handsontables/handsontables.full.js"></script>
<br><br><br>
<table id="exp_data">
    <tr>
        <td colspan="3" align="left">
            <div id="cell_content_border">            
                <textarea id="cell_content" rows="1" placeholder="Select a cell"></textarea>
            <div>
        </td>
    </tr>
    <tr>
        <td align="left">
            <div id="Conditions" class="hide_show_elements"> 
                <h3>Conditions</h3>
                <div id="conditionsArea"></div>
            </div>
        </td>
        <td colspan="2" ></td>
    </tr>
    <tr>
        <td id="Stimuli" class="hide_show_elements" align="left">
            <h3>Stimuli</h3>
            <select id="stim_select"></select>
            <button type="button" id="new_stim_button" class="collectorButton">New Stimuli Sheet</button>
            <span id="stimsArea">
                <div id="stimsheetTable"></div> 
            </span>
        </td>
    
        <td id="Procedure" class="hide_show_elements" align="left">
            <h3>Procedure</h3>
            <select id="proc_select"></select>
            <button type="button" id="new_proc_button" class="collectorButton">New Procedure Sheet</button>
            <span id="procsArea">
                <div id="procSheetTable"></div>
            </span>
        </td>    
        <td id="resp_area">
            <select style="visibility: hidden"><option>Select</option></select>
            <div id="resp_data" class="custom_table"></div>
        </td>
    </tr>
</table>

<script src="../ExperimentEditor/ExpEditorActions.js"></script>
<script src="../ExperimentEditor/ExpEditorFunctions.js"></script>
<script>

sheet_management = {
    current_sheet:'',
    current_coords:[], 
}

handsontable_detected = false;
$(window).bind('keydown', function(event) {
    // hack to get keydown detected for handsontables
    if(handsontable_detected == false){
        handsontable_detected = true;
        $(".handsontableInput").keyup(function(){   
            $("#cell_content").val(this.value);
        });                
        $(".handsontableInput").keydown(function(){   
            $(window).bind('keydown', function(event) {
                if(event.keyCode == "27"){
                    $("#cell_content").val("");
                };
            });
        });
    }    
});

$("#cell_content").on("keyup",function(){ 

    var x_coord = sheet_management.current_coords[0];
    var y_coord = sheet_management.current_coords[1];
    var content = this.value;

    switch (sheet_management.current_sheet){
        case "Conditions":
            handsOnTable_Conditions.setDataAtCell(x_coord,y_coord,content);          
            
            handsOnTable_Conditions.setCellMetObject(x_coord,y_coord,content);
            
            
            break;
        case "Stimuli":
            handsOnTable_Stimuli.setDataAtCell(x_coord,y_coord,content);
            break;
        case "Procedure":
            handsOnTable_Procedure.setDataAtCell(x_coord,y_coord,content);
            break;

    }
    

});

function cell_content_process(thisCellValue,coords,this_sheet){
    $("#cell_content").val(thisCellValue);
    if( sheet_management.current_sheet !== this_sheet |
        sheet_management.current_coords !== coords){            
            // fading solution by victmo on https://stackoverflow.com/questions/7030575/possible-to-fade-out-div-border
            var div = $('#cell_content_border');
            $({alpha:1}).animate({alpha:0}, {
                duration: 1000,
                step: function(){
                    div.css('border-color','rgba(0,0,360,'+this.alpha+')');
                }
            });
            
        }
    sheet_management.current_sheet = this_sheet;
    sheet_management.current_coords = coords;
}

var cell_width = $(window).width()*0.9;
$("#cell_content").css("width",cell_width+"px");


var simulator_on_off = "<?= $simulator_on_off; ?>";
var experiment_files = <?= json_encode($experiment_files); ?>;
var spreadsheets = {};
var alerts_ready = false;

var handsOnTable_Conditions = null;
var handsOnTable_Stimuli    = null;
var handsOnTable_Procedure  = null;

</script>

