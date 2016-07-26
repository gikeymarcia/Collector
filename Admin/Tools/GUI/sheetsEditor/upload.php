<?php

  if(!isset($_SESSION)) { exit; }

  $uploadAlerts=array();
	//user defined variables
	$fileSizeLimit=500000000; //although files below this limit are not being accepted at the moment - maybe a browser issue?
	$acceptedFileTypes=array("jpg","png","jpeg","gif","mp4");	
	
	require '../../Code/initiateCollector.php';
  require $_PATH->get('Header'); 
  require("guiCss.php");
 
	$title = 'Collector GUI';
	$countFiles=count($_FILES['fileToUpload']['name']);
	$target_dir = $_PATH->get("Common");
	$_SESSION['guiSheets']['uploadedFiles']=$_FILES['fileToUpload']['name'];

	for ($i=0; $i<$countFiles;$i++){

		echo "<br>";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"][$i]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		
		if(isset($_POST["submit"])) {
			
			// deal with when the user has not selected a file here!
			if(strcmp($_FILES["fileToUpload"]["tmp_name"][$i],'')==0){
				$message =  "you didn't select any files to upload";
        array_push($uploadAlerts,$message);
				die();
			}
			
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"][$i]);
			if($check !== false) {
				$message = "File is an image - " . $check["mime"] . ".";
        //array_push($uploadAlerts,$message);
        $uploadOk = 1;
			} else {
				$message =  "File is not an image.";
        array_push($uploadAlerts,$message);
				$uploadOk = 0;
			}
		}
		
		// Check if file already exists
		if (file_exists($target_file)) {
			array_push($uploadAlerts,"File already exists.");
      $uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["fileToUpload"]["size"][$i] > $fileSizeLimit) {
      $message = "Sorry, your file is too large.";
      array_push($uploadAlerts,$message);
			$uploadOk = 0;
		}
		
		// Allow certain file formats
		if(in_array($imageFileType,$acceptedFileTypes)!=1) {
			echo "Sorry, only ";
			foreach($acceptedFileTypes as $acceptedFileType){
				echo "$acceptedFileType ";
			}
			echo "files are allowed. ";
      array_push($uploadAlerts,"Sorry, not one of the accepted file types.");
			$uploadOk = 0;
		}
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
      array_push($uploadAlerts,"Sorry, ". basename( $_FILES["fileToUpload"]["name"][$i]). " was not uploaded.");
    // echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$i], $target_file)) {
				echo "The file ". basename( $_FILES["fileToUpload"]["name"][$i]). " has been uploaded.";
        array_push($uploadAlerts,"The file ". basename( $_FILES["fileToUpload"]["name"][$i]). " has been uploaded.");
			} else {
        array_push($uploadAlerts,"Sorry, there was an error uploading your file.");
        echo "Sorry, there was an error uploading your file.";
			}
		}
	}
  $_SESSION['guiSheets']['uploadAlerts']=$uploadAlerts;
  
  header("location:stimList.php")
?>
<form action="stimList.php" method="post">
<button class="collectorButton">Return to stimuli</button>
</form>