<?php

function saveTrialType($elementArray,$trialTypeName){
  
    global $_PATH;
    
    $trialTypeElementsPhp=json_decode($elementArray);  
            
    // Renaming files if task name has changed 
    if($trialTypeName !== ''){   // checking if there is there a long term value for the name to check against
      /* does the new name match the old name*/
      if($trialTypeName !== $trialTypeElementsPhp->trialTypeName){                          //i.e. a new trialType name
        if(file_exists(TRIAL_DIR."/"           .     $trialTypeName . ".txt")){
          unlink(TRIAL_DIR."/"                 .     $trialTypeName . ".txt");              //Delete original file here            
          unlink($_PATH->get('Custom Trial Types')."/". $trialTypeName . "/display.php");   //deleting php file
          $trialTypeCodeDir=$_PATH->get('Custom Trial Types')."/". $trialTypeName;          
          if(count(scandir($trialTypeCodeDir)) <= 2){                                       //if empty
            rmdir($_PATH->get('Custom Trial Types') ."/". $trialTypeName);                  //deleting directory. Don't do this if score.php or other file present.
          }
        }
        $trialTypeName  = $trialTypeElementsPhp->trialTypeName;                             //identify correct name here
      }  
    }
    
    // saving schematic of task (.txt) and task (.php)
    if(!is_dir(TRIAL_DIR)) mkdir($dir, 0777, true);
    
    file_put_contents(TRIAL_DIR."/".$trialTypeName.'.txt',$elementArray); //actual act of saving
    
    if(!isset($_DATA['trialTypeEditor']['currentTrialTypeName'])){
      $_DATA['trialTypeEditor']['currentTrialTypeName']=$trialTypeName;
    }      
    
    require('createTrialType.php');                                      //php file
    
    return $trialTypeElementsPhp;
  }
  
?>