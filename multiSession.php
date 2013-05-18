<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
	ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
	session_start();									// starts the session
	$_SESSION = array();								// reset session so it doesn't contain any information from a previous login attempt
	$_SESSION['Debug']=FALSE;							// turn debug mode on or off   ## SET ##
	require("CustomFunctions.php");						// Load custom PHP functions
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Experiment Login Page</title>
</head>
<?php flush(); ?>
<body>
	
	<div class="ExpContainer">
	
		<div id="LoginPosition"> 
			<h1>Welcome back!</h1>
			<p>This part will run for approximately 15 minutes.								<!--## SET ## give multisession description for your exp-->
			Your goal is to remember things from last time</p>
			
			<form name="Login"  action="login.php"  mehtod="get"  autocomplete="off">
				<p> Please enter the email address you used last time</p>					<!--## SET ## change this for mTurk-->
				<input class="Textbox" id="TextboxComputerTimed" style="width:400px;"  name="Username"  type="text"  value=""  autocomplete="off"/>
				
				<br />
				
				<p> Which session would you like?</p>
				<input class="Textbox"  id="TextboxComputerTimed"  style="width:400px;"  name="Session"  type="text"  value=""  autocomplete="off"/>
				
				<div id="SubmitButton">Submit</div>
			</form>
			
		</div>
	</div>

	<!-- #### how to insert javascript written in separate files #### -->
	<script src="javascript/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<script src="javascript/jsCode.js" type="text/javascript"> </script>

</body>
</html>