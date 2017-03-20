 <style>
  body { 
    color: black; 
    background-color: white; 
  }
  #header { 
    font-size: 180%; 
    text-align: center; 
    margin: 10px 0 40px; 
  }
 
  .tableArea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 10px 30px;
    vertical-align: top;
  }
  textarea { 
    border-radius: 5px;
    border-color: #E8E8E8  ;
  }
  
  
  .helpType_Col { display: none; }
  #helpTypeDefault { display: block; }
  
  #helperBar {
    background-color: #EFE;
    border: 2px solid #6D6;
    border-radius: 8px;
    box-sizing: border-box;
    padding: 2px;
    
  }
  
  #TableForm {
    display: inline-block;
    width: 75%;
    box-sizing: border-box;
  }
  .typeHeader{
    color:blue;
  }
  .typeHeader:hover{
    color:green ;
  }
  #minHelpButton{
    position:fixed;
    font-size:15px;
  }
  #hide_show_table td{
    padding:2px;
  }
  .show_hide_button_select{
    background-color:green;
    color:white;
    padding:2px;
    border-radius:4px;
  }
  .show_hide_button_unselect{
    background-color:blue;
    color:white;
    padding:2px;
    border-radius:4px;
  }
  

  input:checked + .show_hide_span {background-color : blue;}
  .show_hide_span{
    background-color:green;
    border-radius:3px;
    padding: 2px;
    color:white;
  }
  
  
  #helper_title:hover{
    font-weight:      bold;
    cursor:pointer;
  }

</style>


<!-- the helper bar !-->
  
<div id="helperBar">
  <button class="collectorButton" id="helpActivateButton"> Help! </button>
  
  

  
  <div id="helperArea" style="display:none">
    <h1 id="helper_title"> Helper </h1>

    <div>
      Click <a href="https://docs.google.com/document/d/1OCtDrv7VNu8-nD6Pt1FnGSuKkqil2Onm4PVWOL3NthY/edit?usp=sharing" target="_blank"> here</a> for a list of <b>Collector specific</b> javascript functions
    </div>

    
    <h2 id="helpType">Select Cell</h2>
    
    <!-- Help Types -->
    <div class="helpType_Col" id="helpType_Description">
      This is what the name of the conditions the participant/experimentor will be able to see when loading an experiment.
      
    </div>
    
    <div class="helpType_Col" id="helpType_Notes">
      Write notes here. The participants will not be able to see these.
      
    </div>
    
    <div class="helpType_Col" id="helpType_Stimuli1">
      Identify which <b>Stimuli</b> sheet you will be referring to in the procedure.
      
    </div>

    <div class="helpType_Col" id="helpType_Procedure1">
      Identify which <b>Procedure</b> sheet you are running for this condition.
      
    </div>
  
    <div class="helpType_Col" id="helpType_Type">
      <?php
        $surveyTypes=fsDataType_CSV::read("surveyTypes.csv"); //not working
        $surveyTypes=array_slice($surveyTypes,2);
        
        $surveyTypeVector=[];
        foreach($surveyTypes as $surveyType){
          array_push($surveyTypeVector,$surveyType["surveyType"]);
          ?>
          <h3 class="typeHeader" id='header<?=$surveyType["surveyType"]?>' onclick='hideShow("detail<?=$surveyType["surveyType"]?>")'><?=$surveyType["surveyType"]?></h3>
          <div id='detail<?=$surveyType["surveyType"]?>' style='display:none'><?=$surveyType["surveyDetail"]?></div>
          <?php
        }
          $jsonSurveyVector=json_encode($surveyTypeVector);
      ?>
    </div>

    
    <div class="helpType_Col" id="helpTypeDefault">
      Select a cell to see more information about that column.
    </div>
  </div> 

  
</div>


<script type="text/javascript">

$("#helperButton").click(function(){
  if(helperButton.value=="Hide Helper"){
    helperButton.value="Show Helper";
    $("#helperBar").hide();
    TableForm.style.width="100%";//expand width of table
    updateDimensions(stimTable);
  } else {
    helperButton.value="Hide Helper";
    TableForm.style.width="75%";//expand width of table
    updateDimensions(stimTable);
    $("#helperBar").show();
  }
});

function hideShow(x){
  if($('#'+x).is(':visible')) {
    $('#'+x).hide();
  } else {
    $('#'+x).show();
  }
}

$("#helper_title").on("click",function(){
  $("#helper_title").hide();
  $("#helperArea").hide();
  $("#helpActivateButton").show();
});

$("#helpActivateButton").on("click",function(){
  $("#helper_title").show();
  $("#helperArea").show();
  $("#helpActivateButton").hide();
});

//importing json encoded lists from php
var surveyVector=<?=$jsonSurveyVector?>;



function helperActivate(columnName, cellValue){
  $("#helpType").html(columnName);
  columnName = columnName || '';
  var columnCodeName = columnName.replace(/ /g, '');
  
  $("#helperBar").find(".helpType_Col").hide();

  if ($("#helperBar").find("#helpType_" + columnCodeName).length > 0) {
    $("#helperBar").find("#helpType_" + columnCodeName).show();
  } else {
    $("#helperBar").find("#helpTypeDefault").show();
  }
  
  // code for specific helper bars
  if(columnCodeName=="Type"){
    //compare if string is within string
    for(i=0;i<surveyVector.length;i++){
      //remove cases for comparisons
      var surveyValue=surveyVector[i].toLowerCase();
      if(surveyValue.indexOf(cellValue.toLowerCase())==-1){
        $("#header"+surveyVector[i]).hide();
      } else {
        $("#header"+surveyVector[i]).show(); // show header
      }
      
      // show details if only one item fits criterion
      if(surveyValue.localeCompare(cellValue.toLowerCase())==0){ 
        $("#detail"+surveyVector[i]).show();
      } else {
        $("#detail"+surveyVector[i]).hide();
      }
    }    
  }  
}
</script>
