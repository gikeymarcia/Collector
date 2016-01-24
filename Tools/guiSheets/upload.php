<?php adminOnly(); ?>
<form action="stimList.php" method="post">
<button class="collectorButton">Return to stimuli</button>
</form>

<?php

	//user defined variables
	$fileSizeLimit=500000000; //although files below this limit are not being accepted at the moment - maybe a browser issue?
	$acceptedFileTypes=array("jpg","png","jpeg","gif","mp4");	
	
	require '../../Code/initiateCollector.php';
	$title = 'Collector GUI';
	require $_PATH->get('Header');
	$countFiles=count($_FILES['fileToUpload']['name']);
	$target_dir = "../../Experiments/Common/";
	$_SESSION['uploadedFiles']=$_FILES['fileToUpload']['name'];

	for ($i=0; $i<$countFiles;$i++){

		echo "<br>";
		$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"][$i]);
		$uploadOk = 1;
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		// Check if image file is a actual image or fake image
		
		if(isset($_POST["submit"])) {
			
			// deal with when the user has not selected a file here!
			if(strcmp($_FILES["fileToUpload"]["tmp_name"][$i],'')==0){
				echo "you didn't select any files to upload";
				die();
			}
			
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"][$i]);
			if($check !== false) {
				echo "File is an image - " . $check["mime"] . ".";
				$uploadOk = 1;
			} else {
				echo "File is not an image.";
				$uploadOk = 0;
			}
		}
		
		// Check if file already exists
		if (file_exists($target_file)) {
			echo "Sorry, file already exists.";
			$uploadOk = 0;
		}
		
		// Check file size
		if ($_FILES["fileToUpload"]["size"][$i] > $fileSizeLimit) {
			echo "Sorry, your file is too large.";
			$uploadOk = 0;
		}
		
		// Allow certain file formats
		if(in_array($imageFileType,$acceptedFileTypes)!=1) {
			echo "Sorry, only ";
			foreach($acceptedFileTypes as $acceptedFileType){
				echo "$acceptedFileType ";
			}
			echo "files are allowed. ";
			$uploadOk = 0;
		}
		
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			echo "Sorry, your file was not uploaded.";
		// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"][$i], $target_file)) {
				echo "The file ". basename( $_FILES["fileToUpload"]["name"][$i]). " has been uploaded.";
			} else {
				echo "Sorry, there was an error uploading your file.";
			}
		}
	}

?>
