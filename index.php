<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
	
	
	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
 */
	ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
	session_start();									// starts the session
	$_SESSION = array();								// reset session so it doesn't contain any information from a previous login attempt
	
	require 'Code/fileLocations.php';					// sends file to the right place
	require $codeF.'CustomFunctions.php';				// Loads all of my custom PHP functions
	require	$expFiles.'settings.php';					// experiment variables

	$_SESSION['Debug'] = $debugMode;					// turns debug mode on and off
?>
	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="<?php echo $codeF; ?>css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Experiment Login Page</title>
</head>
<?php flush(); ?>
<body>
	
	<div class="ExpContainer">
	
		<div id="LoginPosition"> 
			<h1>Welcome to the experiment!</h1>
			
			<?php echo $expDescription; ?>
			
			<form name="Login"	autocomplete="off" 	action="<?php echo $codeF;?>login.php"	method="get">
				<?php echo $askForLogin;?>
				
				<input class="Textbox"	style="width:400px;"	name="Username"	type="text"	value=""	autocomplete="off"/>
				<br />
				<?php
				if ($showConditionSelector == TRUE) {
					echo '<select class="Dropdown" name="Condition">';
				}
				else {
					echo '<select class="Dropdown Hidden" name="Condition">';
				}
				?>
					<option selected value='Auto'>Auto</option>
					
					<?php
						#### Display conditions as choices
						$Conditions	= GetFromFile($expFiles.'Conditions.txt');					// load from condition file
						$tempCond	= SortByKey($Conditions, 'Number');
						
						for($i=2; $i<count($tempCond); $i++) {							// output all possible condition choices
							echo '<option value=\'' . $tempCond[$i]['Number'] . '\'>' . $tempCond[$i]['Number'] . '</option>';
						}
					?>
				</select>
				<!-- <input class="Button Hidden" type="submit" value="Login"> -->
				<div id="SubmitButton">Submit</div>
			</form>
			
		</div>
	</div>
	
	<div class="Hidden">
		<!-- put things here you want to precache -->
	</div>
	
	<?php
		#### Auto submit Username and Condition to login.php if $_SESSION['Debug']==TRUE
		if($_SESSION['Debug'] == TRUE) {
			echo '<meta http-equiv="refresh" content="1; url='.$codeF.'login.php?Username='.'Debug'.date('U').'&Condition=Auto&Debug=TRUE">';
		}
	?>

	<!-- #### how to insert javascript written in separate files #### -->
	<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<script src="<?php echo $codeF; ?>javascript/jsCode.js" type="text/javascript"> </script>

<!--################# to do #################	-->

</body>
</html>