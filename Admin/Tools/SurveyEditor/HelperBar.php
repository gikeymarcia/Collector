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
    display: inline-block;
    width: 20%;
    background-color: #EFE;
    border: 2px solid #6D6;
    border-radius: 8px;
    box-sizing: border-box;
    padding: 10px;
    vertical-align: top;
    margin-top: 180px;
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
</style>


<!-- the helper bar !-->
  
<div id="helperBar">
  <h1> Helper </h1>
  <h2 id="helpType">Select Cell</h2>
  
  <!-- Help Types -->
  <div class="helpType_Col" id="helpType_Answers">
    Answers can take two forms. If you want a range of numbers, you can type:<br><br>
    <em>FirstNumber::LastNumber</em>
    <br><br><br>
    If you have a list of values you want the participant to choose from, you can list them with a pipe (|) in the middle. E.g.: <br><br>
    <em>Cat|Dog|Bird</em>
    
    <br><br><br>
    Alternatively, you can just leave this value empty if the participant isn't making a choice. For example, for <b>Text</b> or <b> Date</b> questions there are answers to choose from.
  </div>
  
  <div class="helpType_Col" id="helpType_Values">
    <b>Values</b> are stored for each answer given. This is useful if you want to have a score associated with each answer. You need the same number of <b>Values</b> as you have of <b>Answers</b>. This is because <b>Answers need to map onto Values</b>.
    
    <br><br><br>    
    As a result, like <b>Answers</B>, values can take two forms.
     
    If you want a range of numbers, you can type:<br><br>
    <em>FirstNumber::LastNumber</em>
    <br><br><br>
    If you have a list of values you want the participant to choose from, you can list them with a pipe (|) in the middle. E.g.: <br><br>
    <em>Cat|Dog|Bird</em>
    
    <br><br><br>
    Alternatively, you can just leave this value empty if the participant isn't making a choice. For example, for <b>Text</b> or <b> Date</b> questions there are answers to choose from.
  </div>
  
  <div class="helpType_Col" id="helpType_Question">
    Just type in the question in this column.
  </div>

  <div class="helpType_Col" id="helpType_QuestionName">
    Question names are used for storing the data, so each question name has to be unique. Question names are not required for page Breaks.    
  </div>
  
  <div class="helpType_Col" id="helpType_Required">
    Identify in this column whether or not the participant needs to complete this question to proceed. The default assumption is that they do not, but if you want to force a participant to respond to a question, type in "Yes".
  </div>

  <div class="helpType_Col" id="helpType_Shuffle">
    <b>Shuffling</b> is used to randomise the order of stimuli. The default assumption is that there is no shuffling. However, if you want to randomise the order of certain rows, then type in the same value for each of the rows you want shuffled. For example, if I want rows 1,3 and 5 shuffled, but not rows 2 and 4, I could put in these values in this column:
    <br><br>
    shuffle135<br>
    No<br>
    shuffle135<br>
    No<br>
    shuffle135
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

//importing json encoded lists from php
var surveyVector=<?=$jsonSurveyVector?>;

//removing the current study's name from the list (because this list is to prevent duplication)
// studyIndex=listStudyNames.indexOf(currSurveyName.value);
// listStudyNames.splice(studyIndex,1);

// Checks for preventing repeating study names



//checks for preventing repeating sheet names



// var perVar = {}; - probably can delete

function helperActivate(columnName, cellValue){
  
  columnName = columnName || "";
  
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
