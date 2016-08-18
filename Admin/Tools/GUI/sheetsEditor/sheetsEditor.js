

//removing the current study's name from the list (because this list is to prevent duplication)
studyIndex  = listStudyNames.indexOf(currStudyName.value);
listStudyNames.splice(studyIndex,1);


// Checks for preventing repeating study names
var revertStudyName = currStudyName.value;
function checkName(){
  // check if member of array
  if($.inArray(currStudyName.value,listStudyNames)!=-1){
    alert("This is the same name of another study, reverting to unique name");
    $("#currStudyName").val(revertStudyName);
  } else{
    revertStudyName = $("#currStudyName").val();
  }
}


//removing the current study's name from the list (because this list is to prevent duplication)
if (typeof sheetName !== 'undefined'){                            //i.e. if this is not "conditions.csv"
  sheetIndex  = listSheetsNames.indexOf(sheetName.value+'.csv');  
  listSheetsNames.splice(sheetIndex,1);
  var revertSheetName = sheetName.value;
  function checkSheetName(){
    potentialSheetName=sheetName.value+'.csv';
    // check if member of array
    if($.inArray(potentialSheetName,listSheetsNames)!=-1){
      alert("This is the same name of another sheet, reverting to unique name");
      sheetName.value=revertSheetName;
    } else{
      revertSheetName=sheetName.value; // could delete to revert to name when page loaded
    }
    //put in a check to see if there are any illegal symbols here in future version - this is currently being checked after saving  
  }  
}

  var stimTable;
    
    
  var stimContainer = document.getElementById("stimTable");
  stimTable = createHoT(stimContainer, stimData);
    
    // limit resize events to once every 100 ms
    var resizeTimer;
    
    $(window).resize(function() {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function() {
            updateDimensions(stimTable);
        }, 100);
    });
   
$("#submitButton").on("click", function() {
  $("input[name='stimTableInput']").val(JSON.stringify(stimTable.getData()));
});

$("#stimButton").on("click", function() {
  //$("#stimListDiv").show();
  var myWindow = window.open("stimList.php", "", "width=800, height=600");
});

$("#newStimButton").on("click", function() {
  alert("Creating new Stimuli sheet");
});

$("#newProcButton").on("click", function() {
  alert("Creating new Procedure sheet");
});

$("#deleteSheetButton").on("click", function() {
  delConf=confirm("Are you SURE you want to delete this file?");
  if (delConf== true){
    document.getElementById('deleteActivate').click();
  }  
});


// saving code //

var csvSelectedValue  = csvSelected.value; // this to prevent a bug resulting from a user saving after changing the spreadsheet selected

$("#saveButton").on("click", function() { //final checks before saving
  //are there too many empty column headers?
  emptyHeadCols = 0;
  for(i=0; i<stimTable.countCols();i++){
    if(stimTable.getDataAtCell(0,i)==''){
      emptyHeadCols++;
    }
  }
  
  if(emptyHeadCols>1){
    alert("You have an empty header - will not save. Fix before trying to save again.");
  } else {
    
    // dealing with bug that emerges if someone tries to save after changing the csvSelected value;
    if($("#csvSelected").val()!=csvSelectedValue){
      $("#csvSelected").val(csvSelectedValue); 
    }
    $('#submitButton').click();
  }
});

$(window).bind('keydown', function(event) {
    if (event.ctrlKey || event.metaKey) {
      switch (String.fromCharCode(event.which).toLowerCase()) {
      case 's':
        event.preventDefault();
        alert('Saving');
      stimTable.deselectCell();      
      $("#saveButton").click();
        break;
      case 'd':
        event.preventDefault();
      $("#deleteButton").click();
        break;
      }
    }
});

stimTable.addHook('afterSelectionEnd', function(){
  var coords        = this.getSelected();
  var column        = this.getDataAtCell(0,coords[1]);//stimTable.getDataAtCell(0,1); 
  var thisCellValue = this.getDataAtCell(coords[0],coords[1]);
  window['Current HoT Coordinates'] = coords;
  
  helperActivate(column, thisCellValue)
});
// add         helperActivate(column, thisCellValue); to handsontable       afterSelectionEnd: function(){

//        helperActivate(column, thisCellValue);




function helperActivate(columnName, cellValue){
  $("#helpType").html(columnName);
  
  var columnCodeName = columnName.replace(/ /g, '');
  
  if (columnCodeName.indexOf("Score:") !== -1){
    columnCodeName = "Score";
  }
  
  
  $("#helperBar").find(".helpType_Col").hide();

  // Conditions helpers //
  
  if (columnCodeName.indexOf("Procedure") !== -1){
    columnCodeName = "Procedure";
  }
  
  if (columnCodeName.indexOf("Stimuli") !== -1){
    columnCodeName = "Notes";
  }


  if ($("#helperBar").find("#helpType_" + columnCodeName).length > 0) {
    $("#helperBar").find("#helpType_" + columnCodeName).show();
  } else {
    $("#helperBar").find("#helpTypeDefault").show();
  }
  
  // code for specific helper bars
  if(columnCodeName=="TrialType" & cellValue !== null){
    //compare if string is within string
    for(i=0;i<trialTypesJson.length;i++){
      //remove cases for comparisons
      var surveyValue=trialTypesJson[i].toLowerCase();
      if(surveyValue.indexOf(cellValue.toLowerCase())==-1){
        $("#header"+trialTypesJson[i]).hide();
      } else {
        $("#header"+trialTypesJson[i]).show(); // show header
      }
      
      // show details if only one item fits criterion
      if(surveyValue.localeCompare(cellValue.toLowerCase())==0){ 
        $("#detail"+trialTypesJson[i]).show();
      } else {
        $("#detail"+trialTypesJson[i]).hide();
      }
    }    
  }  
}

function hideShow(x){
  if($('#'+x).is(':visible')) {
    $('#'+x).hide();
  } else {
    $('#'+x).show();
  }
}

// during typing into HandsOnTable, update the helper bar
$(document).on("input", ".handsontableInput", function() {
  
  
  
    var coord  = window['Current HoT Coordinates'];

  var y      = coord[1];
  
    var column = stimTable.getDataAtCell(0, y);
    helperActivate(column, this.value);
});

