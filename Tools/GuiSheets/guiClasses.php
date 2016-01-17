<?php

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

?>