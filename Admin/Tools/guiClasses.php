<?php
  
  if(!isset($_SESSION)) { exit; }

	class csvDirInfo {
		public $studyDir = 'to be declared'; // which directory is the study in?
		public $studyName = 'to be declared';
		public function renameStudy($newName){
			$this->studyName=$newName;
		}
	};
	class csvSheetsInfo {
		public $thisSheetName = 'to be declared';
		public $thisSheetFilename = 'to be declared'; //including path
		public $thisSheetFolder = 'to be declared';
		public $stimSheets = array();
		public $procSheets = array();
		public function postSheetInfo($postInfo){
			$postInfo=explode(',',$postInfo);
			$this->thisSheetName=str_ireplace('.csv','',$postInfo[0]);
			$this->thisSheetFolder=$postInfo[1];
			$this->thisSheetFilename="$this->thisSheetFolder/$this->thisSheetName.csv";			
		}		
	}
  
  class surveySheetsInfo {
		public $thisSurveyName = 'to be declared';
		public $thisSurveyFilename = 'to be declared'; //including path
		#public $thisSheetFolder = 'to be declared';
		public $sheetsList = array();
		#public $procSheets = array();
		public function postSurveyInfo($postInfo){
			$postInfo=explode(',',$postInfo);
			$this->thisSurveyName=str_ireplace('.csv','',$postInfo[0]);
			#$this->thisSheetFolder=$postInfo[1];
			$this->thisSurveyFilename="$this->thisSheetName.csv";			
		}		
	}


  class trialTypeElements {
    public $trialTypeName = 'to be declared'; 
    public $elements = array();//'to be declared';
    public $keyboard = array();
//      acceptedKeyboardResponses : "to be declared",
//      correctKeyboardResponsees : "to be declared"
//      );
    public function newElement($elementNo,$elementType,$xPos,$yPos) {
      $this->elements[$elementNo]->type=$elementType;
      $this->elements[$elementNo]->width=20; //this is the default width
      $this->elements[$elementNo]->height=20; //this is the default height
      $this->elements[$elementNo]->xPos=$xPos;
      $this->elements[$elementNo]->yPos=$yPos;
      $this->elements[$elementNo]->stimulus="[not yet added]";
      $this->elements[$elementNo]->responseStim=false;
        
    }
  }

  
  function legitPost ($postInfo,$postArray){
    if (in_array($postInfo,$postArray)){
      return $postInfo;
    }
  }

  
 
?>