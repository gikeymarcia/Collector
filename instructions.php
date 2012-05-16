<?php
	####  good place to pull in values and/or compute things that'll be inserted into the HTML below
	session_start();
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
			<p>	In this study you will see a mixture of intact word pairs (for example, "Word:Pair") and incomplete word pairs (for example, "Word:_____).  When you are shown incomplete pairs you should try to guess which word will fill in the blank.  Sometimes your answer will be correct and sometimes the correct answer will be selected by the computer.  After each guess we will tell you the correct answer and your job is to remember this answer.  When you are shown intact pairs there is no guessing involved but you should still try to remember the correct answer.  At the end of the experiment you will be shown the first word of each pair (Word:_____) and your job is to remember the correct response</p>
			<p>Please take this task very serisouly.  We are interested in how the brain responds to informatin you have generated as compared to when the computer chooses the answer for you.  Your concentration on this task in the lab will help improve our research design for later when we give participants this task in an fMRI brain scanner</p>
						
		</div>
			<!-- ## SET ## This ensures that they read your insturctions.  Participants must correctly answer something about the procedure -->
			<div class="readcheck">
				What is your final task
				<ol class="list">
					<li class="MCbutton wrong"			>	I didn't read the instructions	</li>
					<li class="MCbutton wrong"			>	Categorization task		</li>
					<li class="MCbutton" id="correct"	>	Memory test					</li>
					<li class="MCbutton wrong"			>	I can't read =(		</li>
				</ol>
		</div>
		
		<div class="Hidden" id="RT" >Click to begin the experiment</div>
		
		<form class="" name="Login" action="test.php" method="Post">
			<input	name="RT"			class="RT Hidden"		type="text"	value="0"			/>
			<input	name="Fails"		class="Fails Hidden"	type="text"	value="0"			/>
			<input	name="PrevTrial"	class="Hidden"			type="text"	value="Instruction" />
		</form>
	</div>
	
	<?php
	#### Pre-Cache first trial
	echo '<div class="Hidden">';
		echo show($_SESSION['Trials'][1]['Stimuli']['Cue']);
		echo show($_SESSION['Trials'][1]['Stimuli']['Target']);
		echo show($_SESSION['Trials'][1]['Stimuli']['Answer']);
	echo '</div>';
	
	?>

	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"></script>				<!-- JQuery javascript library (makes basic functions much easier) -->
	<script src="javascript/jsCode.js" type="text/javascript"> </script>						<!-- Your own javascript file -->
</body> </html>