

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

  if(this_sheet_folder == "Stimuli"){
    
    if(stimData[0][0]!=="Item"){ // move all cells to the right to make space for Item column

      for(i=0;i<stimData.length;i++){
      
        for(j=stimData[i].length;j>0;j--){
          stimData[i][j]=stimData[i][j-1];
        }
        
      }
      
      stimData[0][0]="Item";
    
    }
    update_item("initiate");
  }
  

  
  function update_item(initiate){
    
    Array.prototype.isNull = function (){ // by DavidThomas on http://stackoverflow.com/questions/19337761/detect-if-array-has-only-null-values-in-it
      return this.join().replace(/,/g,'').length === 0;
    };

    
    stimData[stimData.length];
    
    var loop_length;

    var stim_col  = stimData[0].indexOf("Item");          
    var no_cols   = stimData[0].length;
    for(i=1;i<stimData.length;i++){
      stimData[i][0] = null;
      if(stimData[i].isNull() == false){
        stimData[i][stim_col]=i+1;
      }

    }
    
    prevent_stim_no_editing(initiate);

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

  if(this_sheet_folder=="Stimuli"){
    prevent_stim_no_editing();   
  }

  function uneditable_cell(instance, td) {
    Handsontable.renderers.TextRenderer.apply(this, arguments);

    td.style.backgroundColor = '#e0e0d1';
  
    return td;
  }  
  
  
  function prevent_stim_no_editing(initiate){
    if(initiate !== "initiate"){
      stimTable.updateSettings({
      cells: function (row, col, prop) {
        var cellProperties = {};

        if (stimTable.getData()[col][prop] === 'Item') {
          cellProperties.editor = false;

          cellProperties.type = 'numeric',
          
          cellProperties.renderer = uneditable_cell;
        } else {
          cellProperties.editor = 'text';
        }

        return cellProperties;
      }
      });      
    }
    
  }


  
  
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




$(window).bind('keydown', function(event) {
  if (event.ctrlKey || event.metaKey) {
    switch (String.fromCharCode(event.which).toLowerCase()) {
    case 's':
      event.preventDefault();
      alert('Saving');
    stimTable.deselectCell();      
    $("#save_status").click();
      break;
    case 'd':
      event.preventDefault();
    $("#deleteButton").click();
      break;
    }
  }
  
  
  if(event.keyCode == 46){
    update_item();
    ajaxSave();
  }

  if(event.keyCode == 27){
    alert("There's a bug with the escape button - your changes will NOT be reverted.");
//        stimTable.Dom.stopImmediatePropagation(event);
    
    // code below is redundant as it stands :-(
    
    stimTable.deselectCell();
    
    ajaxSave();
  }
  
});


stimTable.updateSettings({
  beforeKeyDown: function (e) {
    if (e.keyCode === 27) {
      e.stopImmediatePropagation();
      e.preventDefault();
      
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
  
  if(columnName !== null){
    var columnCodeName = columnName.replace(/ /g, '');
    
    if (columnCodeName.indexOf("Score:") !== -1){
      columnCodeName = "Score";
    }
    
    
    $("#helperBar").find(".helpType_Col").hide();
    $("#helperBar").find(".helpType_Button").hide();
    
    // Conditions helpers //
    
    if (columnCodeName.indexOf("Procedure") !== -1){
      columnCodeName = "Procedure";
    }
    
    if (columnCodeName.indexOf("Stimuli") !== -1){
      columnCodeName = "Notes";
    }


    if ($("#helperBar").find("#helpType_" + columnCodeName).length > 0) {
      $("#helperBar").find("#helpType_" + columnCodeName).show();

      $("#helperBar").find(".helpType_Button").hide();
      $("#helperBar").find("#helpType_" + columnCodeName +"Button").show();
    } else {
      $("#helperBar").find("#helpTypeDefault").show();
    }
    
    // code for specific helper bars
    if(columnCodeName=="TrialType" & cellValue !== null){
      //compare if string is within string
      for(i=0;i<trialTypesJson.length;i++){
        //remove cases for comparisons
        var trial_type_value=trialTypesJson[i].toLowerCase();
        if(trial_type_value.indexOf(cellValue.toLowerCase())==-1){
          $("#header"+trialTypesJson[i]).hide();
        } else {
          $("#header"+trialTypesJson[i]).show(); // show header
        }
        
        // show details if only one item fits criterion
        if(trial_type_value.localeCompare(cellValue.toLowerCase())==0){ 
          $("#detail"+trialTypesJson[i]).show();
        } else {
          $("#detail"+trialTypesJson[i]).hide();
        }
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
  var x      = coord[0];
  var y      = coord[1];
  
  var column = stimTable.getDataAtCell(0, y);
  helperActivate(column, this.value);

  stimTable.getData()[x][y]=this.value;
  
  update_item();
  
  ajaxSave();
  
});