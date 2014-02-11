<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
 
 
	####  good place to pull in values and/or compute things that'll be inserted into the HTML below
	require '../Code/fileLocations.php';					// sends file to the right place
	require $up.$codeF.'CustomFunctions.php';							// Load custom PHP functions
	initiateCollector();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="<?php echo $up.$codeF ?>css/global.css" rel="stylesheet" type="text/css" />
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
		
		<h3 id="waiting">
			Please wait why we load the experiment.... <br />
			This can take up a few minutes if you are on a slow internet connection.
		</h3>
		
		<!-- ## SET ## This ensures that they read your insturctions.  Participants must correctly answer something about the procedure -->
		<div class="readcheck Hidden">
			Should you pay close attention?  (hint: Answer is in the instructions)
			<ol class="list">
				<li class="MCbutton wrong"			>	I don't think so	</li>
				<li class="MCbutton wrong"			>	Nope				</li>
				<li class="MCbutton" id="correct"	>	Yes					</li>
				<li class="MCbutton wrong"			>	I can't read.		</li>
			</ol>
		</div>
		
		<form class="" name="Login" action="<?php echo $up.$codeF; ?>trial.php" method="Post">
			<input	name="RT"			class="RT Hidden"		type="text"	value="0"			/>
			<input	name="Fails"		class="Fails Hidden"	type="text"	value="0"			/>
			<input	name="PrevTrial"	class="Hidden"			type="text"	value="Instruction" />
		</form>
		
	</div>
	
	<?php
	### PRE-CACHES All cues, targets, and answers used in experiment ####
	echo '<div class="Hidden">';
		foreach ($_SESSION['Trials'] as $Trial) {
			echo show($Trial['Stimuli']['Cue'])		. ' ';
			echo show($Trial['Stimuli']['Target'])	. ' ';
			echo show($Trial['Stimuli']['Answer'])	. ' ';
			echo '<br />';
		}
	echo '</div>';
	?>
	
	<script	src="http://code.jquery.com/jquery-1.8.0.min.js"	type="text/javascript"	>	</script>				<!-- JQuery javascript library (makes basic functions much easier) -->
	<script	src="<?php echo $up.$codeF;?>javascript/jsCode.js"				type="text/javascript"	>	</script>				<!-- Your own javascript file -->
</body>
</html>