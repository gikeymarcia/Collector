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
    position: fixed;
    display: inline-block;
    width: 20%;
    background-color: #EFE;
    border: 2px solid #6D6;
    border-radius: 8px;
    box-sizing: border-box;
    padding: 10px;
    vertical-align: top;
    margin-top: 80px ;
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
    right: 100px;
    font-size:15px;
  }
</style>


<!-- the helper bar !-->
  
<div id="helperBar">
  <button id="minHelpButton" class="collectorButton"> _ </button>
  <button class="collectorButton" id="helpActivateButton" style="display:none"> Help! </button>
  <div id="helperArea">
    <h1> Helper </h1>
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

$("#minHelpButton").on("click",function(){
  $("#minHelpButton").hide();
  $("#helperArea").hide();
  $("#helpActivateButton").show();
});

$("#helpActivateButton").on("click",function(){
  $("#minHelpButton").show();
  $("#helperArea").show();
  $("#helpActivateButton").hide();
});

//importing json encoded lists from php
var surveyVector=<?=$jsonSurveyVector?>;

//removing the current study's name from the list (because this list is to prevent duplication)
// studyIndex=listStudyNames.indexOf(currSurveyName.value);
// listStudyNames.splice(studyIndex,1);

// Checks for preventing repeating study names



//checks for preventing repeating sheet names



// var perVar = {}; - probably can delete

function helperActivate(columnName, cellValue){
  $("#helpType").html(columnName);
  
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
