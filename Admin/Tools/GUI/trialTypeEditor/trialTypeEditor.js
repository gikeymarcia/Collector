
var currentElement      =   0;                                              //assumes that we are working from scratch
var inputElementType;                                                       //the type of element that is currently selected to be added to the task. "Select" also included
var elementNo           =   Object.size(trialTypeElements['elements'])-1;   //elements are numbered, e.g. "element0","element1"
var currentResponseNo   =   0;                                              //this needs to be updated whenever you click on an element;
var inputButtonArray    =   ["media","text","input","select"];

elementType('select');                                                      // by default you are in select mode, not creating an element


/* * *
/ I may want to include more code within document.ready function?
* * */

$(document).ready(function() {
  $("#controlPanelRibbon button").click(function() { // when clicking on a button within the control panel ribbon
    var targetElementID = this.value;                // use the value of the clicked button
    
    $("#controlPanelItems > div").hide();            // hide all of the interface in the control panel
    
    $(targetElementID).show();                       // except the one for the clicked button
    
  });
  
  // initiating response array once the page is loaded
  if(typeof(trialTypeElements['responses'])=='undefined'){
    trialTypeElements['responses'] = [[]];
  } else {
    updateClickResponseValues("initiate",trialTypeElements['responses']);    
  }
  
  updateTrialTypeElements();

});


/* structuring code

  have a file for function definitions
    - try to pass in objects through functions rather than refer to global variables

*/



/* * * * *
* button clicking functions
* * * * * */

$("#deleteButton").on("click", function() {
  delConf   =   confirm ("Are you sure you wish to delete?");
  if (delConf == true){
      
    trialTypeElements['elements'][currentElement]['delete']=true
    
    document.getElementById("element"+currentElement).style.display="none";
    $("#displaySettings").hide();
    
    currentStimType.innerHTML   =   "No Element Selected";
    $("#interactionEditorButton").hide();
    $("#displayEditorButton").hide();
    
    updateTrialTypeElements();    
  }
});

$("#loadButton").on("click",function(event){
  if(trialTypeLoading.value=="-select a trial type-"){
    event.preventDefault();
    alert ("You must select a trial type to proceed!!");
  }
});

/* * * * 
* Saving using CTRL S - doesn't suppress event in Firefox
* * * */

$(window).bind('keydown', function(event) {
  if (event.ctrlKey || event.metaKey) {
    switch (String.fromCharCode(event.which).toLowerCase()) {
    case 's':
      event.preventDefault();
      alert('Saving');
      $("#saveButton").click();
      break;
    }
  }
});

/* this function will be added in a later release 
function addDeleteFunction(x){
  alert ("The ability to have multiple click actions for a single element will be added in a later release");
  
  if(document.getElementById("addDeleteFunctionButton"+x).value=="Add Function"){  
    document.getElementById("addDeleteFunctionButton"+x).value="Delete Function";
    
    // update "clickOutcomesAction" for element
      // check what elements are within it
      alert (trialTypeElements['elements'][currentElement]['clickOutcomesAction']); // this has only one element, need to restructure to have multiple elements within it.     
    
  } else {
    document.getElementById("addDeleteFunctionButton"+x).value="Add Function";    
  }
  
}
*/


/* * * * 
*  loading inputs with trialTypeElements settings 
*/


  /* Keyboard */

  acceptedKeyboardResponses.value     =   trialTypeElements['keyboard'].acceptedResponses;
  proceedKeyboardResponses.checked    =   trialTypeElements['keyboard'].proceed;

  

  // identifying which response clicking on the element contributes to, e.g. - whether clicking on element1 contributes to Response1 or Response2
  function updateClickResponseValues(initiateUpdate,responseArray){
    
    var currentElementName = $("#elementNameValue").val();
    
    
    var newRespElement = checkIfResponseListContainsName(responseArray, currentElementName);                        // check if this element is part of new response
    
    responseValuesTidyId.innerHTML="";                                                                              // wipe user friendly list of responses associated with elements
    
    
    responseArray = addNewElementToResponseArray(responseArray,initiateUpdate,newRespElement,currentElementName);   // new Element being added to response array         

    
    responseArray = tidyResponseArray(responseArray);                                                               // remove null values from response array and populates user
                                                                                                                    // friendly response array
    
    trialTypeElements['elements'][currentElement] = updateTrialTypeElementsResponses(trialTypeElements['elements'][currentElement],initiateUpdate);  // update trialTypeElements with input values
   
    $("#responseValuesId").val(JSON.stringify(responseArray));                                                        // update the hidden response code that is 
    
  }
    
  function checkIfResponseListContainsName(responseArray, currentElementName) {
    // check whether the current element is already in the response array
    for(var i=0; i<responseArray.length;i++){
      if(responseArray[i].indexOf(currentElementName)!=-1){
        return false;      //if it is 
      }
    } 
    
    return true;
  }

  function addNewElementToResponseArray(responseArray,initiate,newRespElement,currentElementName){
    if(initiate!="initiate" && newRespElement && currentElementName!=""){                                                      // don't load this at startup
      
        responseArray[0][responseArray[0].length] =   currentElementName;   // add it to the end of the first array in responseArray
        responseNoId.value                        =   0;                    // reset response number to zero (as it is being added to the first array)

    }    
    return responseArray;
  }

  function tidyResponseArray(responseArray){
    
    for(i=0; i<responseArray.length; i++){    
      /* tidying */
      responseArray[i]  =   removeNullValues(responseArray[i]);
      
      /* could add code here to remove blank arrays, but be careful - user may have a blank array in the middle of the response array, which - if deleted, will mess up the order of the arrays. You have been warned. */
      
      /* writing out array in Responses area in form that is legible to user */      
      responseValuesTidyId.innerHTML  +=  "Response "+i+":" +responseArray[i]+"<br>";
           
    }
    
    return responseArray;
  
  }

  
  
  function updateTrialTypeElementsResponses(trialTypeElementStem,initiateUpdate){

    if(initiateUpdate!="initiate"){ // not relevant when initiating page
      trialTypeElementStem['responseValue']  =   responseValueId.value;
      trialTypeElementStem['responseNo']     =   responseNoId.value;
      updateTrialTypeElements();    
    }
    return trialTypeElementStem;
  
  }

  
  /* adjust position of element within responseArray */
  function adjustResponseOrder(responseArray){
       
    var newPos; // the position the element will fit within the array selected. E.g. if the element is added to response 1, newPos will be at the end of response 1.
    
    // add to array that exists or create a new array
    /* adding to array that already exists */
    if(typeof responseArray[responseNoId.value] != 'undefined'){
      newPos = responseArray[responseNoId.value].length;
    } else {
    
    /* creating a new array within responseArray */    
      responseArray[responseNoId.value]   =   [];
      newPos                              =   0;
    }
      
    /* place null value where the element used to be (before being moved). This is tidied later. */
    for(i=0; i<responseArray.length;i++){
      if(responseArray[i].indexOf(elementNameValue.value)!=-1){
        responseArray[i][responseArray[i].indexOf(elementNameValue.value)] = null;
      }
    }
    
    //now that the element's been removed from it's original position, we can add it to the array.
    responseArray[responseNoId.value][newPos]   =   elementNameValue.value;  
    updateClickResponseValues("update",responseArray);                                             
  }

  /* adding elements to the trialType if not clicking on them for editing */
  function tryCreateElement(){        
    if(inputElementType !=  "select"){
      $("#trialEditor").removeClass("CreateMode");
    
    
      elementNo++; // we're not selecting an element, so we're creating one, which means we need a new element number.
      
      xPos  =  Math.round((_mouseX)/elementScale);
      yPos  =  Math.round((_mouseY)/elementScale);
      
      createElementFunction();                          //  add new element to trialType 
      populateDefaultValues();                          //  add default values to this new element
            
      var elemIndex=trialTypeElements['elements'][elementNo];
      
      /* add attributes depending on what type of element */
      if(inputElementType ==  "media"){
        elemIndex['mediaType']   =    "Pic"; // default assumption
      }
      
      if(inputElementType ==  "text" | inputElementType=="input"){
         // to allow more concise coding of the variables
        elemIndex['style']['font-size']         =    textScale+"px";
        elemIndex['style']['color']             =    '';
        elemIndex['style']['font-family']       =    '';
        elemIndex['style']['background-color']  =    '';
      }
      
      if(inputElementType=="input"){
        elemIndex['userInputType']  = "Text";
        elemIndex['height']         = "5"; //overwriting default height
      }
         
      updateTrialTypeElements();
    }
  }
  
  function createElementFunction(){

    if(inputElementType=="input"){
      
      document.getElementById("trialEditor").innerHTML+=
        "<input class='inputElement' type='text' id='element"+elementNo+"' style='position: absolute; width:"+elementScale*20+"px; left:"+_mouseX+"px;top:"+_mouseY+"px' onclick='clickElement("+elementNo+")' name='"+inputElementType+"' readonly>";  
      
    } else {
      // it is not an input, so can create a span instead //
      document.getElementById("trialEditor").innerHTML+=
        "<span class='"+inputElementType+"Element' id='element"+elementNo+"' style='position: absolute; font-size:"+textScale+"px; left:"+_mouseX+"px;top:"+_mouseY+"px; z-index:"+elementNo+"' onclick='clickElement("+elementNo+")' name='"+inputElementType+"'>"+inputElementType+"</span>";
    }
  
  }
  
  function populateDefaultValues(){
    trialTypeElements['elements'][elementNo] = {
      style : {
        position    :   "absolute",
        width       :   20    + "%", 
        height      :   20    + "%",
        left        :   xPos  + "%",
        top         :   yPos  + "%",
        "z-index"   :   elementNo,        
      },
      elementName           :   'element'+elementNo,
      stimulus              :   'not yet added',
      response              :   false,
      trialElementType      :   inputElementType, // repetition here
      clickOutcomesAction   :   '',
      clickOutcomesElement  :   '',
      proceed               :   false,
    };        
  }


  /* updating the trialType */
  
  backupTrialTypeName   =   trialTypeName.value;    //in case the user tries an illegal name
  
  function updateTrialTypeElements(){
    
    $("#trialTypeName").val(trialTypeName.value.replace(/ /g,""));      //  remove whitespace from title
    var trialName   =   $("#trialTypeName").val();                      //  apply this to later code in place of "trialTypeName.value";
    
    trialName=removeIllegalCharacters(trialName);                       //remove illegal characters from title

    trialTypeElements['trialTypeName']                  =   trialName;
    
    document.getElementById("elementArray").innerHTML   =   JSON.stringify(trialTypeElements,  null, 2);
    
    elementType("select");
    
    createTrialType(trialTypeElements); // this may CRASH :-S  

  }

  function removeIllegalCharacters(thisString){
    var illegalChars        =   ['.',' '];              // this probably should be expanded
    var illegalCharPresent  =   false;
    for (var i  = 0; i  < illegalChars.length; i++){
      if(thisString.indexOf(illegalChars[i]) !=  -1){
        alert("Illegal character in name, reverting to acceptable version");
        illegalCharPresent  =   true;
        thisString           =   backupTrialTypeName;
        $("#trialTypeName").val(thisString);            // this will have to change if we use this function on anything other than the title
      }   
    }
    if(illegalCharPresent   ==    false){
      backupTrialTypeName  =  trialTypeName.value;    
    }
    
    return thisString;
  }

  function changeMediaType(){
    trialTypeElements['elements'][currentElement]['mediaType']  =   mediaTypeValue.value;
    updateTrialTypeElements();
    // code here to change image cue if we include media images
  }

  function clickElement(elementX){
    if(inputElementType=="select"){
      
      $("#displayEditorButton").show(1000);                             //this button is hidden at start and after deleting elements
      $("#interactionEditorButton").show(1000);                         //this button is hidden at start and after deleting elements
                            
      currentElement =  elementX;                                       // this is in order to update the global variable "currentElement";
      
      selectUnselectElements(elementNo,currentElement);                 // selecting and unselecting elements
            
      showDisplayEditor();                                              // and hide the other editors
        
      loadConfigs();                                                    // this loads the configurations for the editor
                      
      currentElementAttributes=trialTypeElements['elements'][elementX]; // to simplify later code
      
      editElement(currentElementAttributes);                             // preparing user interface for editing element

    }

  } 
  
  function selectUnselectElements(elementNo,currentElement){
    for(var i=0;i<=elementNo;i++){
      if(i==currentElement){
        document.getElementById("element"+i).className    =   trialTypeElements['elements'][i]['trialElementType']  +   "ElementSelected";
      } else {
        
        if (trialTypeElements['elements'][i] != null) { //code to check whether the element exists or not
          document.getElementById("element"+i).className  =   trialTypeElements['elements'][i]['trialElementType']  +   "Element";
        }
      }
    }
  }

  function showDisplayEditor(){
    $("#displaySettings").hide();
    $("#interactionEditorConfiguration").hide();
    $("#userInputSettings").hide();
    $("#controlPanelItems > div").hide();
    $("#displayEditor").show();  
  }
  
  function editElement(currentElementAttributes){
    
    switch (currentElementAttributes['trialElementType']){

    case "media":
      document.getElementById("inputStimTypeCell").innerHTML="Media Type";
      currentStimType.innerHTML="Media";

      $("#displaySettings").show();
      $("#interactionEditorConfiguration").show();
      $("#userInputTypeValue").hide();
      $("#mediaTypeValue").show();
      $("#textStyle").hide();        
      
    break

    case "text":
      document.getElementById("inputStimTypeCell").innerHTML="Text properties";
      currentStimType.innerHTML="Text";

      $("#displaySettings").show();
      $("#interactionEditorConfiguration").show();
      $("#userInputTypeValue").hide();
      $("#mediaTypeValue").hide();
      $("#textStyle").show();
      
              
      //rather than embed it in above text, i've listed these values below for improved legibility
      textFontId.value  = currentElementAttributes['style']['font-family'];
      textColorId.value = currentElementAttributes['style'].color;
      textSizeId.value  = currentElementAttributes['style']['font-size'].replace("px","");
      textBackId.value  = currentElementAttributes['style']['background-color'];
      
    break

    case "input":      
      document.getElementById("inputStimTypeCell").innerHTML="Input Type";
      
      $("#displaySettings").show();
      $("#interactionEditorConfiguration").show();
      $("#userInputTypeValue").show();
      $("#mediaTypeValue").hide();
      $("#textStyle").show();
      

      //rather than embed it in above text, i've listed these values below for improved legibility
      textFontId.value          =   currentElementAttributes.textFont;
      textColorId.value         =   currentElementAttributes.textColor;
      textSizeId.value          =   currentElementAttributes.textSize;
      textBackId.value          =   currentElementAttributes.textBack;
      document.getElementById("userInputTypeValue").value   =   currentElementAttributes.userInputType;
        
        if(document.getElementById("userInputTypeValue").value    ==    "Text"){
          $('#textTableColorRow').hide();
          $('#textTableBackRow').hide();
        } else {
          $('#textTableColorRow').show();
          $('#textTableBackRow').show();
        }
        
          
        // might add check box and radio in a later release
      break      
    
    }
  }
  
  
  // loading configurations //
  
  function loadConfigs(){

    currentElementAttributes=trialTypeElements['elements'][currentElement];               //to make following code more concise

    $("#elementNameValue").val(currentElementAttributes.elementName);                     // update element name 
    loadResponses(currentElementAttributes);                                              // when loading an element, prepare response (or lack of) values
    currentElementAttributes.mediaType = loadMedia(currentElementAttributes.mediaType);   // update media type, if appropriate
        
    if(typeof(currentElementAttributes.userInputType)!="undefined"){                      // update input type, if appropriate
      userInputTypeValue.value      =   currentElementAttributes.userInputType;
    }

    loadTimings(currentElementAttributes);                                                // Load Timings
    
    // values that don't need functions to load (i.e. exist across elements)
    stimInputValue.value   =   currentElementAttributes.stimulus;
    elementWidth.value     =   currentElementAttributes.style['width'].replace("%","");
    elementHeight.value    =   currentElementAttributes.style['height'].replace("%","");
    
    // positions
    xPosId.value = currentElementAttributes.style['left'].replace("%","");
    yPosId.value = currentElementAttributes.style['top'].replace("%","");
    zPosId.value = currentElementAttributes.style['z-index'];
    
    // click events
    clickOutcomesActionId.value   =   currentElementAttributes.clickOutcomesAction;
    clickOutcomesElementId.value  =   currentElementAttributes.clickOutcomesElement;
  
  }
  
  // load responses (part of load config) //
  function loadResponses(currentElementAttributes){
    if (currentElementAttributes.clickOutcomesAction=="response"){
      $("#clickOutcomesElementId").hide();
      $("#responseValueId").show();
      $("#respNoSpanId").show();
    
      if(typeof(currentElementAttributes.responseValue)!="undefined"){
        $("#responseValueId").val(currentElementAttributes.responseValue);
        $("#responseNoId").val(currentElementAttributes.responseNo);
        clickProceedId.checked  = currentElementAttributes.proceed;
      } else {
        $("#responseValueId").val("");
        $("#responseNoId").val(0);
        clickProceedId.checked  = false;
      }
      updateClickResponseValues("update",trialTypeElements['responses']);

    
    } else {
      $("#clickOutcomesElementId").show();
      $("#responseValueId").hide();
      $("#respNoSpanId").hide();
      populateClickElements();    
    }
  }
  
  // whether loading a picture, video or audio - part of loadConfigs
  function loadMedia(mediaType){
    
    if(typeof(mediaType)!="undefined"){
      /* may not be a media type yet! fix here!!! */
      if(typeof(mediaType)=="undefined"){
        mediaType  = "Pic";
      } 
      $("#mediaTypeValue").val(mediaType);
    } 
    return mediaType; 
  }

  // load onset and offset timings - part of loadConfigs
  function loadTimings(currentElementAttributes){

    if(typeof(currentElementAttributes.onsetTime) == 'undefined'){
      $('#onsetId').val("");
    } else {    
      $('#onsetId').val(currentElementAttributes.onsetTime);
    }
    
    if(typeof(currentElementAttributes.onsetTime) == 'undefined'){
      $('#offsetId').val("");
    } else {
      $('#offsetId').val(currentElementAttributes.offsetTime); 
    }    
  }
  

  function populateClickElements(){
    removeOptions(document.getElementById("clickOutcomesElementId"));   

    var option                      =   document.createElement("option");
    option.text                     =   '';
    document.getElementById("clickOutcomesElementId").add(option);
    clickOutcomesElementId.value    =   trialTypeElements['elements'][currentElement].clickOutcomesElement;  
    var elementList                 =   [];

    
    for(x in trialTypeElements['elements']){
      
      // here be the bug //
      
      if (trialTypeElements['elements'][x] != null){
        elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
        var option    =   document.createElement("option");
        option.text   =   trialTypeElements['elements'][x].elementName;
        option.value  =   trialTypeElements['elements'][x].elementName; 
        document.getElementById("clickOutcomesElementId").add(option);  
      }
    }
  }

  /* Which element type are you adding to the trial */
  function elementType(x){
    inputElementType=x;
    for(i=0;i<inputButtonArray.length;i++){
      if(inputButtonArray[i]==x){
        document.getElementById(inputButtonArray[i]+"Button").className="elementButtonSelected";
      } else {
          if (typeof ("element"+i) != 'undefined') { //code to check whether the element exists or not
            document.getElementById(inputButtonArray[i]+"Button").className="elementButton";
          }     
      }
    }
    if(x!="select"){
      $("#displaySettings").hide();
      $("#userInputTypeValue").value="n/a";

      currentStimType.innerHTML="No Element Selected";
      // for all elements revert formatting to element
      for(i=0;i<=elementNo;i++){
        
        if(typeof (trialTypeElements['elements'][i]) != 'undefined' && document.getElementById("element"+i)!=null) { //code to check whether the element exists or not
          document.getElementById("element"+i).className=trialTypeElements['elements'][i]['trialElementType']+"Element";
        }      
      }
    }  
  }


  /* mouse functions */
  
  function getPositions(ev) { 
  if (ev == null) { ev = window.event }
    var offset = $("#trialEditor").offset(); 
    _mouseX = ev.pageX;
    _mouseY = ev.pageY;
    _mouseX -= offset.left;
    _mouseY -= offset.top;  
  }

  $("#elementTypeList input").on("click", function() {
    if (this.value !== "Select") {
      $("#trialEditor").addClass("CreateMode");
    } else {
      $("#trialEditor").removeClass("CreateMode");
    }
  });

  var showHideRequestInput=false;
  $("#showRequestOptionsId").on("click", function(){
    if(showHideRequestInput==false){
      showHideRequestInput=true;
      $("#newFunctionTable").show();
    } else {
      showHideRequestInput=false;
      $("#newFunctionTable").hide();
    }
  });  

  
  
  
  // creating js trialType

  function createTextElement(element) {   
    return '<div id="' + element.elementName + '" '
         + ' class="textElement" '
         +   getStyleAsHtml(element.style)
         + ">"
         +   element.stimulus
         + '</div>';
  }

  function getStyleAsHtml(attributes) {
    var style = 'style="';
    
    for (var attr in attributes) {
      
      style += attr + ': ' + attributes[attr] + ';   ';
    }
    
    return style + '"';
  }
  
  function createInputElement(element) {
    var type = element.userInputType;
    var stimProp = (type === 'Text') ? 'placeholder' : 'value';
    
    return '<input type="' + type + '"'
         + ' id="' + element.elementName + '"'
         + ' class="inputElement" '
         +   getStyleAsHtml(element.style)
         + ' ' + stimProp + '="' + element.stimulus + '"'
         + '>';
  }

  
  function createMediaElement(element) {
    var type = element.mediaType;
    
    switch (type) {
        case 'Pic': return createPicElement(element);
        case 'Aud': return createAudElement(element);
        case 'Vid': return createVidElement(element);
    }
  }

  function createPicElement(element) {
    return '<img id="' + element.elementName + '"'
         + ' class="mediaElement"'
         + ' src="{getLocation:' + element.stimulus + '}"'
         + ' ' + getStyleAsHtml(element.style)
         + '>';
  }

  function createAudElement(element) {
    return '<audio id="' + element.elementName + '"'
         + ' class="mediaElement"'
         + ' src="{getLocation:' + element.stimulus + '}"'
         + ' autoplay'
         + '>';
  }

  function createVidElement(element) {
    return '<iframe id="' + element.elementName + '"'
         + ' class="mediaElement"'
         + ' ' + getStyleAsHtml(element.style)
         + ' frameborder="0"'
         + ' webkitallowfullscreen mozallowfullscreen allowfullscreen'
         + ' src="{getLocation:' + element.stimulus + '}"'
         + '>';
  }
  
  
  
  
  
  function createTrialType(trialTypeObject){
    
    //response code for later
    // '<textarea id="response" name="Response" placeholder="your responses will go here!"></textarea>';
     
    var newTrialHtmlCode = '';
    for (var elementIndex in trialTypeObject['elements']){
      var element = trialTypeObject['elements'][elementIndex];
      var elType  = element.trialElementType;
      var elHTML;

      switch (elType){
        case "media"  : elHTML = createMediaElement(element); break
        case "text"   : elHTML = createTextElement(element) ; break
        case "input"  : elHTML = createInputElement(element); break
      }
      newTrialHtmlCode += elHTML;
    }       
    enactTrialType(newTrialHtmlCode);
  }



    
  function enactTrialType(newTrialHtmlCode){
    
    window.newTrialTemplate = newTrialHtmlCode;
    
    trialCodePreview.value  = newTrialHtmlCode;
    
    
    trialEditor.innerHTML   = newTrialHtmlCode;
    fillTrialTypeTemplate();
  }
  
  $("#trialEditor").on("click", '*', function() {
    //alert("you just clicked " + this.id);
    
    var elementNumber = this.id.replace("element","");
        
    clickElement(elementNumber);
  });  
  
  
  function associateArray(data) {
    var rowI, rowN = data.length,
        colI, colN = data[0].length;
    var output = [], row, col, val, empty, cols = [];
    
    for (colI=0; colI<colN; ++colI) {
        if (data[0][colI] === null) {
            cols.push(null);
        } else {
            cols.push(data[0][colI].toLowerCase());
        }
    }
    
    for (rowI=1; rowI<rowN; ++rowI) {
        row = {};
        empty = true;
        
        for (colI=0; colI<colN; ++colI) {
            col = cols[colI];
            if (col === null || col === '') continue;
            
            val = data[rowI][colI];
            
            row[col] = val;
            
            if (val !== null && val !== '') empty = false;
        }
        
        if (!empty) output.push(row);
    }
    
    return output;
  }
  
  function fillTrialTypeTemplate() {
    
    var trialData = getTrialData();
      
    var container = $("#trialEditor");
    
    var template = window.newTrialTemplate;//container.html();
   
    container.html(fillTemplate(template, trialData));
}

  
  function getTrialData() {
    var allProcData = getCsvData('procedure');
    var procRow     = window['Current Proc Row'];
    
    if (typeof allProcData[procRow] !== "undefined") {
        var procData = allProcData[procRow];
    } else {
        var procData = allProcData[0];
    }
    
    if (typeof procData['item'] !== "undefined") {
      var item = procData['item'];
    } else {
      var item = 0;
    }
    
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
        if (typeof stimData === "undefined") return [{}];
        var data = stimData.getData();
    } else {
        if (typeof procData === "undefined") return [{}];
        var data = procData.getData();
    }
    return associateArray(data);
  }
   
  /*functions*/

   
  /*
  function locateFileLocalInternet($medStim){
    $mediaPath='$_PATH->get("Media")';      
    $internetLocalLocation=   
  
  
'<?php
 
    // checking whether this is a local file or an online one.
    if(strPos('.$medStim.',"http")!==false){
      $source='.$medStim.';
    } else {
      $source='.$mediaPath.'."/".'. $medStim.';
    }
    ?>';
   
    return $internetLocalLocation;
  }
  
  
  function picProcessing($newTrialTypeElement,$medStim,$elementValue,$initialDisplay,$newTrialHtmlCode){
    
    //code for loading images on other websites... to do later :-(
    
    if($newTrialTypeElement->mediaType=="Pic"){
      
      $medElementName=$newTrialTypeElement->elementName;
      $newTrialHtmlCode=$newTrialHtmlCode.
      locateFileLocalInternet($medStim).    //identifying the location of the stimulus (is it local or elsewhere on the internet?);
      //spacing below is to reduce weird whitespace in file created
   "
    <img id='$medElementName'
    src='<?=".' $source'." ?>'
    $elementValue
    style= '$initialDisplay
    position:absolute; 
    width:".$newTrialTypeElement->width."%;
    height:".$newTrialTypeElement->height."%;
    left:".$newTrialTypeElement->xPosition."%;
    top:".$newTrialTypeElement->yPosition."%;
    z-index:".$newTrialTypeElement->zPosition."; '>
  ";
    }
    return $newTrialHtmlCode;
  }
  
  function vidProcessing($newTrialTypeElement,$medStim,$elementValue,$initialDisplay,$newTrialHtmlCode){
    if($newTrialTypeElement->mediaType=="Video"){

    
// resume here - check if it works with youtube videos!!! and the same for online pictures!!!
  
      $newTrialHtmlCode=$newTrialHtmlCode.
      locateFileLocalInternet($medStim).    //locate file
      "  
  <iframe id ='".$newTrialTypeElement->elementName."'
    ' .$elementValue. '          
    style='".$initialDisplay."
    position:absolute;
    left:".$newTrialTypeElement->xPosition."%;
    top:".$newTrialTypeElement->yPosition."%;
    z-index:".$newTrialTypeElement->zPosition.";'
    width='".$newTrialTypeElement->width."%' 
    height='".$newTrialTypeElement->height."%' 
    frameborder='0'
    webkitallowfullscreen mozallowfullscreen allowfullscreen
    src='<?= ".'$source'." ?>'
    >
  </iframe>
      ";
    }
    return $newTrialHtmlCode;
  }
  
  function audProcessing($newTrialTypeElement,$medStim,$elementValue,$initialDisplay,$newTrialHtmlCode){
    
    $mediaPath='$_PATH->get("Media")';
    
    if($newTrialTypeElement->mediaType=="Audio"){
      $newTrialHtmlCode=$newTrialHtmlCode."  
    <audio id ='".$newTrialTypeElement->elementName."' src='<?= $mediaPath.'/'.$medStim ?>' autoplay>
    </audio>
    ";
    }
    return $newTrialHtmlCode;
  }  
  
  

  // reading each of the html for each element    
  foreach($newTrialTypeArray as $newTrialTypeElement){

    if($newTrialTypeElement   !=    null  && !isset($newTrialTypeElement->delete)){      
      
      // determine whether there is an onset or not, and do not display at start if there is an onset time
      $initialDisplay = "";
      if(isset($newTrialTypeElement->onsetTime)){
        $initialDisplay = "display:none;";
      }
      
      // value if element clicked on
      $elementValue = '';
      if(isset($newTrialTypeElement->responseValue)){
        $elementValue='data-value="'.$newTrialTypeElement->responseValue.'"';
      }
      
      //handling stimuli when they are variables rather than hard coded elements
      list($medStim,$newTrialTypeElement->stimulus) = variableElements($newTrialTypeElement->stimulus); 
      
      if($newTrialTypeElement->trialElementType=="media"){
       
        $newTrialHtmlCode = picProcessing($newTrialTypeElement,$medStim,$elementValue,$initialDisplay,$newTrialHtmlCode); //process IF picture
        $newTrialHtmlCode = vidProcessing($newTrialTypeElement,$medStim,$elementValue,$initialDisplay,$newTrialHtmlCode); //process IF video
        $newTrialHtmlCode = audProcessing($newTrialTypeElement,$medStim,$elementValue,$initialDisplay,$newTrialHtmlCode); //process IF audio
                    
      }
            
      $newTrialHtmlCode = textProcessing($newTrialTypeElement,$elementValue,$initialDisplay,$newTrialHtmlCode); //process IF text
      $newTrialHtmlCode = inputProcessing($newTrialTypeElement,$elementValue,$initialDisplay,$newTrialHtmlCode); //process IF input
      
    }
  }
  
  // Javascript code is being added here
  $newTrialJSCode="<script>";

  function jsClickActions($newTrialTypeElement,$jsAction){    
    //$clickElement='onclick="'.$newTrialTypeElement->elementName.'Click()"';
    
    // Click Actions 
    $clickActions = glob('clickActionsBackend/*.php'); // list files in clickActions folder
    
    foreach ($clickActions as $clickAction){
      require ("$clickAction");                             // adds javascript code for the action if called to $jsAction
    }  
    return($jsAction);
  }
  
  function jsClickResponses($elementName,$responseElements,$jsResp){
    for($i=0;$i<count($responseElements);$i++){
       
      if(in_array($elementName,$responseElements[$i])){
        $jsResp="respArray[$i]=".'$("#'.$elementName.'").data("value");
        updateResp();';                
      }       
    }
    return($jsResp);
  }
  
  foreach($newTrialTypeArray as $newTrialTypeElement){
    if($newTrialTypeElement   !=    null){
      
      $jsAction='';
      $jsResp='';
      $jsProc='';
    
    if($newTrialTypeElement->clickOutcomesAction!=''){
      $jsAction = jsClickActions($newTrialTypeElement,$jsAction);   // add code for clicking actions
      $jsResp   = jsClickResponses($newTrialTypeElement->elementName,$responseElements,$jsResp);
    }
    
    // Proceed elements 
    if($newTrialTypeElement->proceed=="true"){
      $jsProc="Collector.submit();";
    }

      $newTrialJSCode=$newTrialJSCode.'
      $("#'.$newTrialTypeElement->elementName.'").click(function(){
        '.$jsAction.'
        '.$jsResp.'
        '.$jsProc.'
      });';
    }      
  }
          
          
  //handling onset and offsets
  $jsOnsetCode='';
  $jsOffsetCode='';
  foreach($newTrialTypeArray as $newTrialTypeElement){
    if(isset($newTrialTypeElement->onsetTime)){
      $onsetMS=$newTrialTypeElement->onsetTime*1000;
      $jsOnsetCode.='
      $("#'.$newTrialTypeElement->elementName.'").delay('.$onsetMS.').fadeIn(0);
      ';
    }
    if(isset($newTrialTypeElement->offsetTime)){
      $offsetMS=$newTrialTypeElement->offsetTime*1000;
      if(isset($onsetMS)){
        $offsetMS-=$onsetMS;
      }
      $jsOffsetCode.='
      $("#'.$newTrialTypeElement->elementName.'").delay('.$offsetMS.').fadeOut(0);
      ';
    }          
  }
  $newTrialJSCode=$newTrialJSCode.$jsOnsetCode.$jsOffsetCode;

          
  //record keyboard responses here

  $keyboardResponse='';
  if(!empty($newTrialTypeInfo->keyboard->acceptedResponses)){
  $keyboardResponse = "
  $(window).bind('keydown', function(event) {
    switch (String.fromCharCode(event.which).toLowerCase()) {";
    for($i = 0; $i<strlen($newTrialTypeInfo->keyboard->acceptedResponses); $i++){
      $currentKey=$newTrialTypeInfo->keyboard->acceptedResponses[$i];
      $keyboardResponse = $keyboardResponse."
      case '$currentKey':
        event.preventDefault(); // not sure this is working in firefox
        response.value='$currentKey'; // change response //";
        if($newTrialTypeInfo->keyboard->proceed="true"){
          $keyboardResponse=$keyboardResponse."
          Collector.submit();
          ";
        }        
        $keyboardResponse=$keyboardResponse."   
        break;            
      ";
    }       
    $keyboardResponse = $keyboardResponse."}
    });";
  }
      
  $newTrialJSCode=$newTrialJSCode.$keyboardResponse."
    respArray=[];
    function updateResp(){
      response.value=respArray;
    }
    </script>";
  
  $newTrialCode=$newTrialHtmlCode.$newTrialJSCode;
  
  
  $trialPath  = $_PATH->get('Custom Trial Types');
  $trialPath .= "/" . $_DATA['trialTypeEditor']['currentTrialTypeName'];
  
  if (!is_dir($trialPath)){
  
    mkdir($trialPath, 0777, true); 

  }
  file_put_contents($trialPath."/display.php",$newTrialCode);
  
  ?>
     
  }
  
  
  */
  
  
  
  