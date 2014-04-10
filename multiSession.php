<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    ini_set('auto_detect_line_endings', true);          // fixes problems reading files saved on mac
    session_start();                                    // starts the session
    $_SESSION = array();                                // reset session so it doesn't contain any information from a previous login attempt

    require 'Code/fileLocations.php';                   // sends file to the right place
    require $codeF.'CustomFunctions.php';               // Loads all of my custom PHP functions
    require $expFiles.'Settings.php';                   // experiment variables
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="<?php echo $codeF; ?>css/global.css" rel="stylesheet" type="text/css" />
	<title>Experiment Login Page</title>
</head>
<?php flush(); ?>
<body data-controller=multiSession>
    <!-- redirect if Javascript is disabled -->
    <noscript>
        <meta http-equiv="refresh" content="0;url=<?php echo $codeF; ?>nojs.php" />
    </noscript>

    <div class=cframe-outer>
        <div class=cframe-inner>
            <div class='cframe-content textcenter login-pos'>

    			<h1>Welcome back!</h1>
    			<p>This part will run for approximately 15 minutes.	             <!--## SET ## give multisession description for your exp-->
    			Your goal is to remember things from last time</p>

    			<form name=Login class=collector-form action="login.php"  method=get  autocomplete=off>
    				<label>Your username from last time:                         <!--## SET ## change for mTurk -->
    				    <input name=Username type=text value="" />
                    </label>

    				<label> Which session would you like?
    				    <input name=Session type=text value="" />
    				</label>

                    <br />
				    <input class=button type=submit value="Login" />

			     </form>
			</div>
		</div>
	</div>

	<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"> </script>
	<script src="<?php echo $codeF; ?>javascript/collector_1.0.0.js" type="text/javascript"> </script>

</body>
</html>