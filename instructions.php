<?php
	####  good place to pull in values and/or compute things that'll be inserted into the HTML below
	if(isset($_SESSION) == FALSE) { session_start(); }
	require("CustomFunctions.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Experiment Instructions</title>
</head>

<body>
	<div id="centerContent">
		<div class="Instruct">
			<!-- ## SET ## Change the instructions text to match your task. You start and end new paragraphs with <p>paragraph here</p>-->
			<p>	In this study you will be studying some stuff then you will need to recall that stuff.
				After each bunch of stuff there will be some kind of memory task.<br />
				Please pay close attention to the things we are showing you.</p>
				
			<p> As many paragraphs as you would like can go here.  Instructions are done.  Time for you to move onto the experiment</p>
			
		</div>
			<!-- ## SET ## This ensures that they read your insturctions.  Participants must correctly answer something about the procedure -->
			<div class="readcheck"> Should you pay close attention?  (hint: Answer is in the instructions)
				<ol class="list">
					<li class="MCbutton wrong" >I don't think so</li>
					<li class="MCbutton wrong" >Nope</li>
					<li class="MCbutton" id="correct">Yes</li>
					<li class="MCbutton wrong" >I can't read.</li>
				</ol>
		</div>
		<div class="Hidden" id="RT" >Click to begin the experiment</div>
		
		<form class="" name="Login" action="test.php" method="Post">
			<input name="RT" class="RT Hidden" type="text" value="0" />
			<input name="Fails" class="Fails Hidden" type="text" value="0" />
			<input name="PrevTrial" class="Hidden" type="text" value="Instruction" />
		</form>
	</div>
	
	<?php
	#### Pre-Cache first trial
	echo '<div class="Hidden">';
		echo show($_SESSION['Trials'][1]['Stimuli']['Cue']);
		echo show($_SESSION['Trials'][1]['Stimuli']['Target']);
		echo show($_SESSION['Trials'][1]['Stimuli']['Answer']);
		foreach ($_SESSION['Trials'] as $trial) {
			echo show($trial['Stimuli']['Cue']);
			echo show($trial['Stimuli']['Target']);
			echo show($trial['Stimuli']['Answer']);
		}
	echo '</div>';
	
	?>

	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"></script>					<!-- JQuery javascript library (makes basic functions much easier -->
	<script src="javascript/jsCode.js" type="text/javascript"> </script>						<!-- Your own javascript file -->
</body> </html>