
/* Configurations and preparing global variables */
var elementScale  = 8; // config
var textScale     = 20;

var currentElement      =   0;                                              //assumes that we are working from scratch
var trialTypeElements   =   <?= $jsontrialTypeElements ?>;                  //the object containing all the trialTypeInformation
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
        elemIndex['textSize']    =    textScale;
        elemIndex['textColor']   =    '';
        elemIndex['textFont']    =    '';
        elemIndex['textBack']    =    '';
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
      width                 :   20, 
      height                :   20,
      xPosition             :   xPos,
      yPosition             :   yPos,
      zPosition             :   elementNo,
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
        $("#displaySettings").show();
        $("#interactionEditorConfiguration").show();
        document.getElementById('userInputTypeValue').style.visibility="hidden";
        document.getElementById('mediaTypeValue').style.visibility="visible";
        currentStimType.innerHTML="Media";
      break

      case "text":
        $("#displaySettings").show();
        $("#interactionEditorConfiguration").show();
        document.getElementById('mediaTypeValue').style.visibility="hidden";
        currentStimType.innerHTML="Text";
        document.getElementById("inputStimTypeCell").innerHTML="Text properties";
        // userInputTypeValue is being used for both media and input types - this could probably be tidier by keeping them separate
        inputStimSelectCell.innerHTML=
          '<table>'+ 
            '<tr>'+
              '<td>font size</td>'+
              '<td><input type="number" id="textSizeId" onchange="adjustTextSize()" value='+textScale+' min="1" style="width:50px">px</td>'+
            '</tr>'+
            '<tr>'+
              '<td>color</td>'+
              '<td><input type="text" id="textColorId" onkeyup="adjustTextColor()" placeholder="color"></td>'+
            '</tr>'+
            '<tr>'+
              '<td>font</td>'+
              '<td><input type="text" id="textFontId" onkeyup="adjustTextFont()" placeholder="font"></td>'+
            '</tr>'+
            '<tr>'+
              '<td>background-color</td>'+
              '<td><input type="text" id="textBackId" onkeyup="adjustTextBack()" placeholder="background-color"></td>'+
            '</tr>'
          '</table>';
               
        //rather than embed it in above text, i've listed these values below for improved legibility
        textFontId.value   =  currentElementAttributes.textFont;
        textColorId.value  =  currentElementAttributes.textColor;
        textSizeId.value   =  currentElementAttributes.textSize;
        textBackId.value   =  currentElementAttributes.textBack;
        
      break      

      case "input":      
        document.getElementById("inputStimTypeCell").innerHTML="Input Type";
        $("#displaySettings").show();
        $("#interactionEditorConfiguration").show();        
        document.getElementById('mediaTypeValue').style.visibility="invisible";
        document.getElementById('userInputTypeValue').style.visibility="visible";
        currentStimType.innerHTML="Input";
        textTableSize       =     '<tr id="textTableSizeRow">'+
                                    '<td>size</td>'+
                                    '<td><input type="number" id="textSizeId" onchange="adjustTextSize()" value='+textScale+' min="1" style="width:50px">px</td><br>'+
                                  '</tr>';
        textTableColor      =     '<tr id="textTableColorRow">'+
                                    '<td>color</td>'+
                                    '<td><input type="text" id="textColorId" onkeyup="adjustTextColor()" placeholder="e.g. red, #FF0000" ></td>'+
                                  '</tr>';
        textTableFont       =     '<tr id="textTableFontRow">'+
                                    '<td>font</td>'+
                                    '<td><input type="text" id="textFontId" onkeyup="adjustTextFont()" placeholder="font"></td>'+
                                  '</tr>';
        textTableBackColor  =     '<tr id="textTableBackRow">'+
                                    '<td>background-color</td>'+
                                    '<td><input type="text" id="textBackId" onkeyup="adjustTextBack()" placeholder="background-color"></td>'+
                                  '</tr>';
                          
        /* if handling text, not button input, then need to remove font color and background-color due to inflexibility of placeholders */

        inputStimSelectCell.innerHTML=
          '<select id="userInputTypeValue" onchange="adjustUserInputType()">'+
            '<option>Text</option>'+
            '<option>Button</option>'+
          '</select>'+
          '</td><br>'+
          '<table>'+
            textTableSize+
            textTableColor+
            textTableFont+
            textTableBackColor+
          '</table>';

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
    stimInputValue.value          =   currentElementAttributes.stimulus;
    elementWidth.value            =   currentElementAttributes.width;
    elementHeight.value           =   currentElementAttributes.height;
    
    // positions
    xPosId.value                  =   currentElementAttributes.xPosition;
    yPosId.value                  =   currentElementAttributes.yPosition;
    zPosId.value                  =   currentElementAttributes.zPosition; 
    
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
          console.dir(trialTypeElements['elements'][i]);
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

  function mouseMovingFunctions(){
    if(inputElementType=="select"){
      
      /* text element style */
      var css   =  '.textElement:hover{ border-color        :     black;'+
                                        'background-color   :     transparent;'+
                                        'text-shadow        :     -1px -1px 0 #000,1px -1px 0 #000,-1px 1px 0 #000,1px 1px 0 #000; }';
      
      applynewStyle();
 
      /* media element style */
      var css   =   '.mediaElement:hover{ border-color      :     white;'+
                                          'background-color :     green;'+
                                          'color            :     white }';
      
      applynewStyle();

      
      /* input elemnt style */
      var css   =   '.inputElement:hover{ border-color      : green;'+
                                          'background-color : green;'+
                                          'color            : blue }';
                                          
      applynewStyle();
   }     
      
     
    /* change all element styles to nonHover version */ 
    if(inputElementType!="select"){
      //text elements
      var css   =   '.textElement:hover{  border-color      :   transparent;'+
                                          'background-color :   transparent;'+
                                          'color            :   blue}';
      applynewStyle();

      //media elements
      var css   =   '.mediaElement:hover{ border-color      :   blue;'+
                                          'background-color :   transparent;'+
                                          'color:blue }';
      applynewStyle();
      
      //input elements
      var css   =   '.inputElement:hover{ border            :   1px solid #cccccc;'+
                                          'background-color :   white;'+
                                          'color            :   white}';
      applynewStyle();
    }
    //keeping this function local;
    function applynewStyle(){
      style = document.createElement('style');

      if (style.styleSheet) {
          style.styleSheet.cssText = css;
      } else {
          style.appendChild(document.createTextNode(css));
      }
      document.getElementsByTagName('head')[0].appendChild(style);  
    }
  }
  

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
