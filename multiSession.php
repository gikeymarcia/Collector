<?php
ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
@session_destroy();									// destory any possible previous sessions and suppress warnings
//session_start();									// starts a new session
$_SESSION['Debug']=FALSE;							// turns debug mode on and off   ## SET ##
require("CustomFunctions.php");						// Loads all of my custom PHP functions
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

<body>
	
	<div class="ExpContainer">
	
		<div id="LoginPosition"> 
			<h1>Welcome back!</h1>
			<p>This part will run for approximately 15 minutes.								<!--## SET ## give multisession description for your exp-->
			Your goal is to remember things from last time</p>
			
			<form name="Login" action="login.php" mehtod="get">
				<p> Please enter the email address you used last time</p>					<!--## SET ## change this for mTurk-->
				<input class="Textbox" id="TextboxComputerTimed" style="width:400px;" name="Username" type="text" value=""/>
				
				<br />
				
				<p> Which session would you like?</p>
				<input class="Textbox" id="TextboxComputerTimed" style="width:400px;" name="Session" type="text" value=""/>
				
				<div id="SubmitButton">Submit</div>
			</form>
			
		</div>
	</div>

	<!-- #### how to insert javascript written in separate files #### -->
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
	<script src="javascript/jsCode.js" type="text/javascript"> </script>

</body>
</html>