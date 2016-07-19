<?php

/*
  	GUI - Trial type editor by Anthony Haffey

	Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>

    */

  // retrieving elements in readable form
  $newTrialTypeInfo = file_get_contents("GUI/newTrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'].".txt");
  $newTrialTypeInfo = json_decode($newTrialTypeInfo);
  
  // deal with retrieval as an array or as an object
  if(isset($newTrialTypeInfo->elements)){ 
    $newTrialTypeArray  = $newTrialTypeInfo->elements;
  } else {
    $newTrialTypeArray  = $newTrialTypeInfo['elements'];
  }
  if(isset($newTrialTypeInfo->responses)){
    $responseElements   = $newTrialTypeInfo->responses;
  } 
  /*
  else {
    $responseElements=$newTrialTypeInfo['responses'];      
  }
  */
  
  //declaring the $newTrialHtmlCode, i.e. starting the file
  $newTrialHtmlCode = '<div style="position: relative; width:800px; height:800px">
  <textarea id="response" name="Response" placeholder="your responses will go here!"></textarea>
  '; 
    
   
  /*functions*/

  //handling stimuli when they are variables rather than hard coded elements
  function variableElements($stimulus){
    if(strPos($stimulus,"$")!==false){

      // good place for security checks for use of $?
      
      $stimulus=str_replace('$','',$stimulus);
      $stimulus='$_EXPT->get("'.$stimulus.'")';
      $medStim = $stimulus;
    } else {
      
      $medStim = "'" . $stimulus ."'";
    } 
    return array($medStim,$stimulus);
  }
  
  
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
  
  function textProcessing($newTrialTypeElement,$elementValue,$initialDisplay,$newTrialHtmlCode){
    if($newTrialTypeElement->trialElementType=="text"){

    $newTrialHtmlCode=$newTrialHtmlCode.'<div id ="'.$newTrialTypeElement->elementName.'" 
    ' .$elementValue. '
    style=" '.$initialDisplay.'
    position:absolute;
    width:'.$newTrialTypeElement->width.'%;
    height:'.$newTrialTypeElement->height.'%;
    left:'.$newTrialTypeElement->xPosition.'%;
    top:'.$newTrialTypeElement->yPosition.'%;
    z-index:'.$newTrialTypeElement->zPosition.';
    font-size:'.$newTrialTypeElement->textSize.'px;
    color:'.$newTrialTypeElement->textColor.';
    background-color:'.$newTrialTypeElement->textBack.';
    font-family:'.$newTrialTypeElement->textFont.';">
    <?= "'.$newTrialTypeElement->stimulus.'" ?>
  </div>
  ';
    }  
    return $newTrialHtmlCode;
  }
  
  function inputProcessing($newTrialTypeElement,$elementValue,$initialDisplay,$newTrialHtmlCode){
  
    if($newTrialTypeElement->trialElementType=="input"){
      $newTrialHtmlCode=$newTrialHtmlCode.'<input id ="'.$newTrialTypeElement->elementName.'" 
      ' .$elementValue. '      
    type="'.$newTrialTypeElement->userInputType.'"
    
    style=" '.$initialDisplay.'
    position          :   absolute;
    width             :   '.$newTrialTypeElement->width.'%;
    height            :   '.$newTrialTypeElement->height.'%;
    left              :   '.$newTrialTypeElement->xPosition.'%;
    top               :   '.$newTrialTypeElement->yPosition.'%;
    z-index           :   '.$newTrialTypeElement->zPosition.';
    font-size         :   '.$newTrialTypeElement->textSize.'px;
    color             :   '.$newTrialTypeElement->textColor.';
    background-color  :   '.$newTrialTypeElement->textBack.';
    font-family       :   '.$newTrialTypeElement->textFont.';
    " value= "'.$newTrialTypeElement->stimulus.'";   />
  '; 

    }
    return $newTrialHtmlCode;
  }

  // reading each of the html for each element    
  foreach($newTrialTypeArray as $newTrialTypeElement){

    if($newTrialTypeElement   !=    null){      
      
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
    
    /* Click Actions */
    $clickActions = glob('GUI/clickActionsBackend/*.php'); // list files in clickActions folder
    
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
    
    /* Proceed elements */
    if($newTrialTypeElement->proceed=="true"){
      $jsProc="$('form').submit();";
    }

      $newTrialJSCode=$newTrialJSCode.'
      $("#'.$newTrialTypeElement->elementName.'").click(function(){
        '.$jsAction.'
        '.$jsResp.'
        '.$jsProc.'
      });';
    }      
  }
          
  //currently handling onset and offsets separately - for simplicity
  $jsOnsetCode='';
  $jsOffsetCode='';
  foreach($newTrialTypeArray as $newTrialTypeElement){
    if(isset($newTrialTypeElement->onsetTime)){
      $splitOnset=explode(":",$newTrialTypeElement->onsetTime);
      $onsetMS=24*60*1000*60000*$splitOnset[0]+60*1000*$splitOnset[1]+1000*$splitOnset[2];
      if (isset($splitOnset[3])){ //ms data is not always present
        $onsetMS+$splitOnset[3];                
      }
      //$newTrialTypeElement->onsetTime=DurationFormatUtils.formatDuration($newTrialTypeElement->onsetTime, "SSS");
      //time($newTrialTypeElement->onsetTime);
      $jsOnsetCode=$jsOnsetCode.'
      $("#'.$newTrialTypeElement->elementName.'").delay('.$onsetMS.').fadeIn(0);
      ';
    }
    if(isset($newTrialTypeElement->offsetTime)){
      $splitOffset=explode(":",$newTrialTypeElement->offsetTime);
      $offsetMS=24*60*1000*60000*$splitOffset[0]+60*1000*$splitOffset[1]+1000*$splitOffset[2];
      if (isset($splitOffset[3])){ //ms data is not always present
        $offsetMS+$splitOffset[3];                
      }
      //$newTrialTypeElement->onsetTime=DurationFormatUtils.formatDuration($newTrialTypeElement->onsetTime, "SSS");
      //time($newTrialTypeElement->onsetTime);
      $jsOffsetCode=$jsOffsetCode.'
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
                  $('form').submit();
                  ";
                }
                
                "
                //proceed trial if keyboard response is meant to do so
                
                break;            
            ";
          }       
          $keyboardResponse = $keyboardResponse."}
          });";

      /* Steps:
        -add "keyboard responses" to newTrialTypeInfo object
        -go through each character separately.
          -check whether it's in the "correct keyboard response"
          
        
      /*
      
      "textSize": 12,
      "textColor": "default",
      "textFont": "default"
      */

      }
      
  $newTrialJSCode=$newTrialJSCode.$keyboardResponse."
    respArray=[];
    function updateResp(){
      response.value=respArray;
    }
    </script>";
  
  $newTrialCode=$newTrialHtmlCode.$newTrialJSCode;
  if (!file_exists("../Experiments/_Common/TrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'])){
    //nothing happens
  //} else {
    mkdir("../Experiments/_Common/TrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName'], 0700); 
  }
  file_put_contents("../Experiments/_Common/TrialTypes/".$_DATA['trialTypeEditor']['currentTrialTypeName']."/display.php",$newTrialCode);
  
  ?>
      
      
<script>
  //alert("creating trial type");
</script>