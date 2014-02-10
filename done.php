<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
	ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
	session_start();									// start the session at the top of each page
	
	// if someone skipped to done.php without doing all trials
	if ($_SESSION['finishedTrials'] <> TRUE) {
		header("Location: http://www.youtube.com/watch?v=oHg5SJYRHA0");			// rick roll
		exit;
	}
	
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require("CustomFunctions.php");						// Loads all of my custom PHP functions
	
	#### TO-DO ####
	$finalNotes = '';
	/*
	 * Write code that looks at previous logging in activity and gives recommendations as to whether or not to include someone
	 * ideas:
	 *		if someone has logged in more than once, flag them
	 * 		if someone has 1 login and no ends then say they're likely good
	 * 		if someone already has 1 finish then say so	
	 */
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
		if ($_SESSION['mTurkMode'] == TRUE) {
			echo '<h3>Your verification code is: '.$_SESSION['verifCode'].'</h3>';
		}
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
						$_SESSION['Condition']['Procedure'],
						$_SESSION['Condition']['Condition Description'],
						$_SERVER['HTTP_USER_AGENT'],
						$_SERVER["REMOTE_ADDR"],
						$finalNotes
					 );
	$UserDataHeader = array(
						'Username' ,
						'Date' ,
						'Session #' ,
						'Begin/End?' ,
						'Condition #',
						'Words File',
						'Procedure File',
						'Condition Description',
						'User Agent Info',
						'IP',
						'Inclusion Notes'
					 );
	if (is_file("subjects/Status.txt") == FALSE) {					// if the file doesn't exist, write the header
 		arrayToLine ($UserDataHeader, "subjects/Status.txt");
 	}
	arrayToLine ($UserData, "subjects/Status.txt");					// write $UserData to "subjects/Status.txt"
	########
	
	$_SESSION = array();											// clear out all session info
	session_destroy();												// destry the session so it doesn't interfere with any future experiments
	
?>
	<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<script src="javascript/jsCode.js" type="text/javascript"> </script>
</body>
</html>