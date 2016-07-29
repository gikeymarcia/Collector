function saveTextAsFile() // solution by NatureShade at http://stackoverflow.com/questions/609530/download-textarea-contents-as-a-file-using-only-javascript-no-server-side 
{
    var textToWrite = JSON.stringify(trialTypeElements);//Your text input;
    var textFileAsBlob = new Blob([textToWrite], {type:'text/plain'});
    var fileNameToSaveAs = "elementArray.json";//Your filename;

    var downloadLink = document.createElement("a");
    downloadLink.download = fileNameToSaveAs;
    downloadLink.innerHTML = "Download File";
    if (window.webkitURL != null)
    {
        // Chrome allows the link to be clicked
        // without actually adding it to the DOM.
        downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
    }
    else
    {
        // Firefox requires the link to be added to the DOM
        // before it can be clicked.
        downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
        downloadLink.onclick = destroyClickedElement;
        downloadLink.style.display = "none";
        document.body.appendChild(downloadLink);
    }

    downloadLink.click();
}

/* * * * * *
* Adjust events
* * * * * */

function adjustKeyboard(){
  trialTypeElements['keyboard']={
    acceptedResponses:acceptedKeyboardResponses.value,
    proceed:proceedKeyboardResponses.checked,    
  }
  updateTrialTypeElements();
}

function adjustStimulus(){
  var stimText=stimInputValue.value;
  
  // check if it's a text input - which would mean we change the placeholder, not the text //
  if (typeof(trialTypeElements['elements'][currentElement]['userInputType'])!="undefined"){
    
    if(trialTypeElements['elements'][currentElement]['userInputType']=="Text"){
      // wipe value (so that user can see placholder) and update placeholder value
      document.getElementById("element"+currentElement).value         =   '';
      document.getElementById("element"+currentElement).placeholder   =   stimText;
    } else {
      // update value for buttons
      document.getElementById("element"+currentElement).value         =   stimText;
    }
    
  }
  document.getElementById("element"+currentElement).innerHTML   =   stimText;
  
  if(stimText.indexOf('$') != -1 ){  // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
    stimInputValue.style.color="blue";
  } else {
    stimInputValue.style.color="black";
  }

  trialTypeElements['elements'][currentElement]['stimulus']=stimText;//update trialTypeElements
  updateTrialTypeElements();

}

function adjustElementName(){
  var elementNameText=elementNameValue.value;
  document.getElementById("element"+currentElement).innerHTML=elementNameText;
  
  if(elementNameText.indexOf('$') != -1 ){  // may need to put checks on this to prevent e.g.s like "[stim]asdfadsf"
    elementNameValue.style.color="blue";
  } else {
    elementNameValue.style.color="black";
  }
  trialTypeElements['elements'][currentElement]['elementName']=elementNameText;//update trialTypeElements
  updateTrialTypeElements();
}

function adjustHeight(){
  if(Number(yPosId.value) + Number(elementHeight.value) > 100){
    elementHeight.value = 100-yPosId.value; // temporary correction will still allow user to create something bigger than the screen
  }
  newHeight = elementScale*elementHeight.value;
  newHeight = newHeight +"px";
  document.getElementById("element"+currentElement).style.height = newHeight;
  trialTypeElements['elements'][currentElement]['style']['height']=elementHeight.value+"px";//update trialTypeElements
  updateTrialTypeElements();
}

function adjustWidth(){
  if( Number(xPosId.value) + Number(elementWidth.value) > 100){
    elementWidth.value = 100-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
  }
  newWidth = elementScale*elementWidth.value;
  newWidth = newWidth +"px";
  document.getElementById("element"+currentElement).style.width = newWidth;
  trialTypeElements['elements'][currentElement]['style']['width']=elementWidth.value+"px";//update trialTypeElements
  updateTrialTypeElements();
}

  // position adjustment:

  function adjustXPos(){
    if( Number(xPosId.value) + Number(elementWidth.value) > 100){
      xPosId.value= 100- elementWidth.value; // temporary correction will still allow user to create something bigger than the screen
    }  
    newXPos=(Number(xPosId.value)*elementScale) +"px";
    document.getElementById("element"+currentElement).style.left = newXPos; //-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
    trialTypeElements['elements'][currentElement]['style']['left']=xPosId.value+"%";//update trialTypeElements
    updateTrialTypeElements();
  }


  function adjustYPos(){
    if( Number(yPosId.value) + Number(elementHeight.value) > 100){
      yPosId.value= 100- elementHeight.value; // temporary correction will still allow user to create something bigger than the screen
    }  
    newYPos=(Number(yPosId.value)*elementScale) +"px";
    document.getElementById("element"+currentElement).style.top = newYPos; //-xPosId.value; // temporary correction will still allow user to create something bigger than the screen
    trialTypeElements['elements'][currentElement]['style']['top']=yPosId.value+"%";//update trialTypeElements
    updateTrialTypeElements();
  }

  function adjustZPos(){
    trialTypeElements['elements'][currentElement]['z-index']=zPosId.value;//update trialTypeElements
    
    //update style here!!!
    document.getElementById("element"+currentElement).style.zIndex = zPosId.value;
    
    updateTrialTypeElements();
  }


  // adjusting click events
  function adjustClickProceed(){
    trialTypeElements['elements'][currentElement]['proceed']=clickProceedId.checked;//update trialTypeElements
    updateTrialTypeElements();
  }

  function adjustOutcomeElement(){
      trialTypeElements['elements'][currentElement]['clickOutcomesElement']=clickOutcomesElementId.value;
      updateTrialTypeElements();
  }

  function adjustClickOutcomes(){
    if(clickOutcomesActionId.value=="response"){
      $("#clickOutcomesElementId").hide();
      $("#responseValueId").show();
      $("#respNoSpanId").show();
      updateClickResponseValues("update",trialTypeElements['responses']);
    } else {
      $("#clickOutcomesElementId").show();
      populateClickElements();    
      $("#responseValueId").hide();
      $("#respNoSpanId").hide();
      trialTypeElements['elements'][currentElement]['clickOutcomesElement']=clickOutcomesElementId.value;
    }

    trialTypeElements['elements'][currentElement]['clickOutcomesAction']=clickOutcomesActionId.value;//update trialTypeElements
    updateTrialTypeElements();
    // could add preview of outcome e.g. temp fade of another element if that's the action
    currentResponseNo=responseNoId.value;

  }
  
  // text adjustments
  
  function adjustTextBack(){
    document.getElementById("element"+currentElement).style.backgroundColor=textBackId.value;
    if(textBackId.value==""){
      trialTypeElements['elements'][currentElement]['style']['textBack']="";
    } else {
      trialTypeElements['elements'][currentElement]['style']['background-color']=textBackId.value;//update trialTypeElements
    }
    updateTrialTypeElements();
  }

  function adjustTextColor(){
    document.getElementById("element"+currentElement).style.color=textColorId.value;
    if(textColorId.value==""){
      trialTypeElements['elements'][currentElement]['style']['color']="";
    } else {
      trialTypeElements['elements'][currentElement]['style']['color']=textColorId.value;//update trialTypeElements
    }
    updateTrialTypeElements();
  }

  function adjustTextFont(){
    document.getElementById("element"+currentElement).style.fontFamily=textFontId.value;
    if(textFontId.value==""){
      trialTypeElements['elements'][currentElement]['style']['font-family']="";
    } else {
      trialTypeElements['elements'][currentElement]['style']['font-family']=textFontId.value;//update trialTypeElements
    }
    updateTrialTypeElements();
  }

  function adjustTextSize(){
    document.getElementById('element'+currentElement).style.fontSize=(textSizeId.value)+"px";
    
    trialTypeElements['elements'][currentElement]['style']['font-size']=textSizeId.value+"px";//update trialTypeElements
      
    updateTrialTypeElements();
  }


  
function adjustTime(onsetOffset){
  timeElement=document.getElementById(onsetOffset+"Id");
  if(timeElement.value=="00:00"){
    timeElement.style.color="grey";
  } else {
    timeElement.style.color="blue";
  }
  trialTypeElements['elements'][currentElement][onsetOffset+'Time']=timeElement.value;//update trialTypeElements
  updateTrialTypeElements();
}


function adjustUserInputType(){
  var element = document.getElementById("element"+currentElement);
  element.parentNode.removeChild(element);
  currentXPos   =   xPosId.value        *   elementScale;
  currentYPos   =   yPosId.value        *   elementScale;
  currentWidth  =   elementWidth.value  *   elementScale;
  currentHeight =   elementHeight.value *   elementScale;
  document.getElementById("trialEditor").innerHTML+="<input class='inputElement' type='"+userInputTypeValue.value+"' id='element"+currentElement+"' style='position: absolute;left:"+currentXPos+"px;top:"+currentYPos+"px; width:"+currentWidth+"px; height:"+currentHeight+"px' onclick='clickElement("+elementNo+")' name='"+currentElement+"' value='"+stimInputValue.value+"' readonly>";  
  
  document.getElementById('element'+currentElement).style.color             =   textColorId.value;
  document.getElementById('element'+currentElement).style.fontFamily        =   textFontId.value;
  document.getElementById('element'+currentElement).style.fontSize          =   (textSizeId.value)+"px";
  document.getElementById('element'+currentElement).style.backgroundColor   =   textBackId.value;
          
  
  /* hide and show attributes depending on whether it's a text input or now. This is because placeholders (whice are used in text variables) are awkward to color dynamically */
  if(document.getElementById("userInputTypeValue").value    ==    "Text"){

    //  prevent element attributes changing the color on the editor;
    document.getElementById('element'+currentElement).style.color             =   "grey";
    document.getElementById('element'+currentElement).style.backgroundColor   =   "white";

    //  hide the attributes themselves
    $('#textTableColorRow').hide();
    $('#textTableBackRow').hide();
  } else {
    $('#textTableColorRow').show();
    $('#textTableBackRow').show();
  }
  
  trialTypeElements['elements'][currentElement]['userInputType']  =   userInputTypeValue.value;
  updateTrialTypeElements();
}

// other functions

  /* used for cleaning array of null values*/
  function removeNullValues(x){ 
    var cleanArray=[];          // only add values that exist to this array (i.e. not NULL)
    for(j=0; j<x.length;j++){
      if(x[j]){
        cleanArray.push(x[j]);  
      }    
    }
    return cleanArray;
  }

function removeOptions(selectbox) // this solution was from Fabiano at http://stackoverflow.com/questions/3364493/how-do-i-clear-all-options-in-a-dropdown-box
  {
    var i;
    for(i=selectbox.options.length-1;i>=0;i--)
    {
        selectbox.remove(i);
    }
  }

  
  function fillTrialTypeTemplate() {
    
    var trialData = getTrialData();
    
    var container = $("#trialEditor");
    
    var template = container.html();
    
    container.html(fillTemplate(template, trialData));
}

  function getTrialData() {
    var procData    = getCsvData('procedure')[0];
    var item        = procData['item'];
    var allStimData = getCsvData('stimuli');
    
    if (typeof allStimData[item-2] !== "undefined") {
      var stimData = getCsvData('stimuli')[item-2];
    } else {
      var stimData = {};
    }
    
    return {
      inputs: {
        stim: procData,
        proc: stimData,
        extra: {}
      }
    }
  }

  function fillTemplate(template, data) {
    
    var self = data;
      
    return template.replace(/\[[^\]]+\]/g, function(keyWithBrackets) {
      
      var key = keyWithBrackets.substr(1, keyWithBrackets.length-2); // pull off the brackets
      key = key.toLowerCase();
          
      if (typeof self.inputs.proc[key] !== "undefined") {
        return self.inputs.proc[key];
      } else if (typeof self.inputs.stim[key] !== "undefined") {
        return self.inputs.stim[key];
      } else {
        return keyWithBrackets;
      }
    }).replace(/{[^}]+}/g, function(keyWithBrackets) {
      var key = keyWithBrackets.substr(1, keyWithBrackets.length-2); // pull off the brackets
      key = key.toLowerCase();
        
      if (typeof self.inputs.extra[key] !== "undefined") {
        return self.inputs.extra[key];
      } else {
        return keyWithBrackets;
      }
    });
  }

  function getCsvData(type) {
    if (type === 'stimuli') {
      var data = stimData.getData();
    } else {
      var data = procData.getData();
    }
    return associateArray(data);
  }
  

// future development

function supportClickOutcomes(){
  // this may be developed in version 2 to help users use this functionality; Need a list somewhere of all functions.   
}; 