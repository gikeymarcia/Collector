<!-- Collector 1.00.00 alpha1
	A program for running experiments on the web
	Copyright 2012 Mikey Garcia & Nate Kornell
-->

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
<?php flush(); ?>
<body>	

<?php
	if($_SESSION['NextExp'] == FALSE) {
		## SET ## Change the email address to your email
		echo '<div id="donePage">
				<h2>Thank you for your participation!</h2>
				<p>If you have any questions about the experiment please email YOURemail@yourdomain.com</p>
			  </div>';
	} else {
		echo "<h2>Experiment will resume in 5 seconds.</h2>";
		$nextLink = 'http://'.$_SESSION['NextExp'];
		echo '<meta http-equiv="refresh" content="5; url='.$nextLink.'login.php?Username='.$_SESSION['Username'].'&Condition=Auto">';
	}
	
		
	// readable($_SESSION['Trials']);
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
?>
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
	<script src="javascript/jsCode.js" type="text/javascript"> </script>
</body>
</html>