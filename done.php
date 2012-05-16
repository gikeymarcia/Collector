<?php
	ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
	session_start();									// start the session at the top of each page
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require("CustomFunctions.php");						// Loads all of my custom PHP functions
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Done!</title>
</head>

<body>	

<?php
	## SET ## Change the email address to your email
	echo '<div id="donePage">
			<h2>Thank you for your participation!</h2>
			<p>If you have any questions about the experiment please email gikeymarcia@gmail.com</p>
		  </div>';
		
	// readable($_SESSION['Trials']);
	
	// Place google form embed code below (not necessary now that i've written FinalQuestions.php)
?>

<?php	
	#### Record info about the person ending the experiment to StatusFile.txt
	$UserData = array(
						$_SESSION['Username'] ,
						date('c') ,
						"Session " . $_SESSION['Session'] ,
						"Session End" ,
						"Condition# {$_SESSION['Condition']['Number']}",
						$_SESSION['Condition']['Stimuli'],
						$_SESSION['Condition']['Order'],
						$_SESSION['Condition']['Condition Description'],
						$_SERVER['HTTP_USER_AGENT']
					 );
	$UserDataHeader = array(
						"Username" ,
						"Date" ,
						"Session #" ,
						"Begin/End?" ,
						"Condition #",
						"Words File",
						"Order File",
						"Condition Description",
						"User Agent Info"
					 );
	if (is_file("subjects/Status.txt") == FALSE) {					// if the file doesn't exist, write the header
 		arrayToLine ($UserDataHeader, "subjects/Status.txt");
 	}
	arrayToLine ($UserData, "subjects/Status.txt");					// write $UserData to "subjects/Status.txt"
	########
	
	
	session_destroy();												// destry the session so it doesn't interfere with any future experiments
	
	#### TO DO ####
	/*
	 * write code that allows me to automatically send someone to a new experiment
	 * this code should pass on username and i guess it will have to use Auto condition selection
	 */
?>
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
	<script src="javascript/jsCode.js" type="text/javascript"> </script>
</body>
</html>