	<?php
	ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
	session_start();									// starts the session
	@session_destroy();									// destory any possible previous sessions and suppress warnings
	$_SESSION['Debug']=FALSE;							// turns debug mode on and off   ## SET ##
	$selector = TRUE;									// ## SET ##; Show (TRUE) or hide (FALSE) the condition selector
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
				<h1>Welcome to the experiment!</h1>
				<p>This experiment will run for approximately 25 minutes.								<!--## SET ## give intro description for your exp-->
				Your goal is to learn some information</p>
				
				<form name="Login" action="login.php" method="get">
					<p> Please enter your UCLA email address below</p>					<!--## SET ## change this for mTurk-->
					
					<input class="Textbox" style="width:400px;" name="Username" type="text" value=""/>
					
					<br />
					<?php
					if ($selector == TRUE) {
						echo '<select class="Dropdown" name="Condition">';
					}
					else {
						echo '<select class="Dropdown Hidden" name="Condition">';
					}
					?>
						<option selected value='Auto'>Auto</option>
						
						<?php
							#### Display conditions as choices
							$Conditions = GetFromFile("Conditions.txt");					// load from condition file
							$tempCond = SortByKey($Conditions, 'Number');
							
							for($i=2; $i<count($tempCond); $i++) {							// output all possible condition choices
								echo '<option value=\'' . $tempCond[$i]['Number'] . '\'>' . $tempCond[$i]['Number'] . '</option>';
							}
						?>
					</select>
					<br />
					<!-- <input class="Button Hidden" type="submit" value="Login"> -->
					<div id="SubmitButton">Submit</div>
				</form>
				
			</div>
		</div>
		
		<div class="Hidden">
			<!-- put things here you want to precache -->
		</div>
		<?php
		
		#### Auto submit Username and Condition to login.php if debugging is on
		if($_SESSION['Debug'] == TRUE) {
			echo '<meta http-equiv="refresh" content="1; url=login.php?Username='.'Debug'.date('U').'&Condition=Auto&Debug=TRUE">';
		}
		?>
	
		<!-- #### how to insert javascript written in separate files #### -->
		<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
		<script src="javascript/jsCode.js" type="text/javascript"> </script>
	
	<!--################# to do #################
	
	-->
	
	</body>
	</html>