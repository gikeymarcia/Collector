<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	
	require 'fileLocations.php';							// sends file to the right place
	require $up.$expFiles.'settings.php';					// experiment variables
	require 'CustomFunctions.php';							// Loads all of my custom PHP functions
	initiateCollector();
	
	
	// setting up easier to use and read aliases(shortcuts) of $_SESSION data
	$condition		=& $_SESSION['Condition'];
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=& $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Procedure']['Trial Type']));
		$item		=  trim(strtolower($currentTrial['Procedure']['Item']));
	
	
	// if we hit a *newfile* then the experiment is over (this means that we don't ask FinalQuestions until the last session of the experiment)
	if($item == '*newfile*') {
		header("Location: done.php");
		exit;
	}
	
	
	// if just coming from instructions then record that data into a file
	if(@$_POST['PrevTrial'] == 'Instruction') {
		$instructFile = $up.$dataF.'InstructionsData.txt';
		if(is_file($instructFile) == FALSE) {
			$instructHeader = array('Username','Timestamp', 'RT','Fails');
			arrayToLine($instructHeader,$instructFile);
		}
		$instructData = array(	$_SESSION['Username'], date('c'), $_POST['RT'], $_POST['Fails'] );
		arrayToLine($instructData,$instructFile);
	}
	
	
	// if there is another item coming up then set it as $nextTrial
	if(array_key_exists($currentPos+1, $_SESSION['Trials'])) {
		$nextTrial =& $_SESSION['Trials'][$currentPos + 1];
	} else { $nextTrial = FALSE;}
	
	
	// if there has been a previous item then set it as $previousTrial
	if($currentTrial > 1) {
		$previousTrial =& $_SESSION['Trials'][$currentPos - 1];
	} else { $previousTrial = FALSE;}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Trial</title>
</head>
<?php flush();	?>
<body>
	
<?php
	// go to stepout page if this is a stepout trial
	if ($trialType == 'stepout') {
		$page = trim( $currentTrial['Procedure']['Procedure Notes'] );
		echo '<meta http-equiv="refresh" content="0; url='.$page.'">';
	}
	
	
	// variables I'll need and/or set in trialTiming() function
	$timingReported = trim(strtolower($currentTrial['Procedure']['Timing']));
	$formClass	= '';
	$time		= '';
	$minTime	= 'not present (unless set)';
	
	#### Presenting different trial types ####
	$trialFail = FALSE;																			// this will be used to show diagnostic information when a specific trial isn't working
	switch ($trialType) {
			
		// Audio trial type (no input)
		case 'audio':
			$compTime = 5;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			echo '<div class="WordWrap PreCache">
					<audio autoplay><source src="'.$up.$expFiles.$cue.'" /></audio>
				  </div>';
			// the hidden form below collects RT and displays the 'Done' button for user timed trials
			echo '<div id="buttPos" class="PreCache">
					<form class="'.$formClass.'" action="postTrial.php" method="post">
						<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
						<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  	</form>
				  </div>';
			break;
			
			
		// Copy trial type
		case 'copy':
			$compTime = 8;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div class="WordWrap PreCache">
					<span class="left">'.$cue.'</span>
					<span class="divider">:</span>
					<span class="right">'.$target.'</span>
				  </div>
				  
				  <div class="WordWrap PreCache">
				  	<span class="leftcopy">'.$cue.'</span>
				  	<span class="dividercopy">:</span>
				  	<form class="'.$formClass.' leftfloat"  autocomplete="off"  action="postTrial.php"  method="post">
					  	<input 	name="Response" type="text" value=""			class="Textbox"			autocomplete="off" />
					  	<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
						<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
						<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
						<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
					  </form>
				  </div>';
			break;
			
			
		// FreeRecall trial type
		case 'freerecall':
			$compTime = 60;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			$prompt =& $currentTrial['Procedure']['Procedure Notes'];
			echo '<div id="centerContent" class="PreCache">
				<div class="Prompt">' . $prompt . '</div>
					<form class="'.$formClass.'"  autocomplete="off"  action="postTrial.php"  method="post">
						<textarea rows="20" cols="60" name="Response" class="PreCache" wrap="physical" value=""></textarea>	<br />
						<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
						<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
						<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
						<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
					</form>
					</div>';
			break;
			
			
		// Instruct trial type (no input)
		case 'instruct':
			$compTime = 5;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div id="centerContent" class="PreCache">
					<div class="Instruct">'. $currentTrial['Procedure']['Procedure Notes'].'</div>
				  </div>';
			// the hidden form below collects RT and displays the 'Done' button for user timed trials
			echo '<div id="buttPos" class="PreCache">
					<form class="'.$formClass.'" action="postTrial.php" method="post">
						<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
						<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  	</form>
				  </div>';
			break;
			
			
		// JOL trial type
		case 'jol':
			$compTime = 8;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div id="JOLpos" class="PreCache">
					<div id="jol">How likely are you to correctly remember this item on a later test?</div>
					<div id="subpoint" class="gray">Type your response on a scale from 0-100 using the entire range of the scale</div>';
			
			echo '<form class="'.$formClass.'"  autocomplete="off"  action="postTrial.php"  method="post">
					<input	name="Response"	type="text"	value=""			class="Textbox"			autocomplete="off" />	<br />
					<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
					<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
					<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
					<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
				  </form>
				 </div>';
			break;
			
			
		// MCpic trial type
		case 'mcpic':
			$compTime = 8;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			$MCbuttons 		= $MultiChoiceButtons;
			$itemsPerRow	= 4;
			if($_SESSION['MCbutton'] == FALSE) {			// shuffle button position before presenting for the 1st time
				shuffle($MCbuttons);						// turn this line off to maintain the same choice order between-subjects
				$_SESSION['MCbutton'] = $MCbuttons;
			}
			// show the image
			echo '<div class="pic PreCache">
					'. show($cue).
				 '</div>';		
			// display the MC button choices
			$count = 0;
			echo '<div id="ButtonArea">';
			foreach ($_SESSION['MCbutton'] as $aButton) {
				echo '<span class="TestMC PreCache">'.$aButton.'</span>';
				$count++;
				if ($count == $itemsPerRow) {
					echo '<br style="clear: both;"/>';
					$count = 0;
				}
			}
			echo '</div>';
			
			$formClass = $formClass.' center';
			echo '<form class="'.$formClass.'" action="postTrial.php" method="post">
					<input	name="Response"	type="text"	value="no press"	class="Textbox Hidden"	/>
					<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
					<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
					<input	name="RT"		type="text"	value="RT"	class="RT Hidden"		/>
				  </form>';
			break;
			
			
		// Passage trial type (no input)
		case 'passage':
			$compTime = 60;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div class="passage PreCache">'.fixBadChars($cue).'</div>
				  <div id="end" class="PreCache">End of Passage</div>';
				  $formClass = $formClass.' center';
			// the hidden form below collects RT and displays the 'Done' button for user timed trials
			echo '<div id="buttPos" class="PreCache">
					<form class="'.$formClass.'" action="postTrial.php" method="post">
						<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
						<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  	</form>
				  </div>';
			break;
			
			
		// Study trial type (no input)
		case 'study':
			$compTime = 5;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div class="WordWrap PreCache">
					<span class="left">'.$cue.'</span>
					<span class="divider">:</span>
					<span class="right">'.$target.'</span>
				  </div>';
			// the hidden form below collects RT and displays the 'Done' button for user timed trials
			echo '<div id="buttPos" class="PreCache">
					<form class="'.$formClass.'" action="postTrial.php" method="post">
						<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
						<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  	</form>
				  </div>';
			break;
			
			
		// StudyPic trial type (no input)
		case 'studypic':
			$compTime = 5;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div class="pic PreCache">
					'. show($cue).
				 '</div>';
			echo '<div class="picWord PreCache">'.$target.'</div>';
			$formClass = $formClass.' center';
			// the hidden form below collects RT and displays the 'Done' button for user timed trials
			echo '<div id="buttPos" class="PreCache">
					<form class="'.$formClass.'" action="postTrial.php" method="post">
						<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
						<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  	</form>
				  </div>';
			break;
			
			
		// Test trial type
		case 'test':
			$compTime = 8;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div class="WordWrap PreCache">
					<span class="leftcopy">'.$cue.'</span>
					<span class="dividercopy">:</span>
					<form class="'.$formClass.' leftfloat"  autocomplete="off"  action="postTrial.php"  method="post">
						<input	name="Response"	type="text"	value=""			class="Textbox Right PreCache"	autocomplete="off" />
						<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
						<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
						<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
						<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
					</form>
				  </div>';
			break;
			
			
		// TestPic trial type
		case 'testpic':
			$compTime = 8;					// time in seconds to use for 'computer' timing
			trialTiming();					// determines timing and user/computer timing mode
			
			echo '<div class="pic PreCache">
					'. show($cue).
				 '</div>';
			$formClass = $formClass.' center';
			
			echo '<form class="'.$formClass.' PreCache"  autocomplete="off"  action="postTrial.php"  method="post" class="PreCache">
					<input  name="Response" type="text" value=""			class="Textbox picWord PreCache" autocomplete="off" />	<br />
					<input	name="RT"		type="text"	value="RT"			class="RT Hidden"		/>
					<input	name="RTkey"	type="text"	value="no press"	class="RTkey Hidden" 	/>
					<input	name="RTlast"	type="text"	value="no press"	class="RTlast Hidden" 	/>
					<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
				  </form>';
			break;
			
			
		## ADD ## your new trial should be a switch statement here (e.g., case 'yourTrialName':)  don't forget to add the break; after outputting your new trial
		
		default:
			echo '<h2>Could not find the following trial type: <b>'.$trialType.'</b></h2>
					<p>Check your procedure file to make sure everything is in order. 
					All information about this trial is dispalyed below.</p>';
			$trialFail = TRUE;
			$time = 'user';
			// default trial is always user timing so you can click 'Done' and progress through the experiment
			echo '<div id="buttPos" class="PreCache">
				<form name="UserTiming" class="UserTiming" action="postTrial.php" method="post">
					<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
					<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
			  	</form>
			  </div>';
			break;
	}
	
	// hidden field that JQuery/JavaScript uses to submit the trial to postTrial.php
	echo '<div id="Time"	class="Hidden">' . $time . '</div>';
	echo '<div id="minTime"	class="Hidden">' . $minTime . '</div>';
	
	?>
		<!-- the following lines are placeholders for a debug function that shows timer values -->
		<br>
		<div id="showTimer" class="Hidden">
			<div> Start (ms):	<span id="start">	</span>	</div>
			<div> Current (ms):	<span id="current">	</span>	</div>
			<div> Timer (ms):	<span id="dif">		</span>	</div>
		</div>
		
	<?php
	

	#### Pre-Cache Next trial ####
	echo '<div class="Hidden">';
			echo show($nextTrial['Stimuli']['Cue']).'	<br />';
			echo show($nextTrial['Stimuli']['Target']).'<br />';
			echo show($nextTrial['Stimuli']['Answer']).'<br />';
	echo '</div>';
	
	
	
	#### Diagnostics ####
	if ($trialDiagnostics == TRUE OR $trialFail == TRUE) {
		echo "<div id='Diagnostics'>
				<ul>
					<li> Condition #: 			{$_SESSION['Condition']['Number']}					</li>
					<li> Condition Stim File:	{$_SESSION['Condition']['Stimuli']}					</li>
					<li> Condition Order File:	{$_SESSION['Condition']['Procedure']}				</li>
					<li> Condition description:	{$_SESSION['Condition']['Condition Description']}	</li>
					<br/>
					<li> Trial Number:			{$currentPos}										</li>
					<li> Trial Type:			{$trialType}										</li>
					<li> Post Trial:			{$currentTrial['Procedure']['Post Trial']}			</li>
					<li> Trial timing:			{$currentTrial['Procedure']['Timing']}				</li>
					<li> Trial Time (seconds):	{$time}												</li>
					<br/>
					<li> Cue: ".				show($cue)."										</li>
					<li> Target:".				show($target)."										</li>
					<li> Answer:".				show($answer)."										</li>
				</ul>";
		readable($currentTrial, "information loaded about the Current trial");
		readable($_SESSION['Trials'], "information loaded THE ENTIRE EXPERIMENT!!!");
		echo "</div>";
	} 
	#### Diagnostics ####
?>
	<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<script src="javascript/trial.js" type="text/javascript"> </script>
</body>
</html>