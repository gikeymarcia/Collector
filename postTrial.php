<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
	require("CustomFunctions.php");							// Load custom PHP functions
	initiateCollector();
	
	#### setting up aliases (for later use)
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=  $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Procedure']['Trial Type']));
		$postTrial	=  trim(strtolower($currentTrial['Procedure']['Post Trial']));
	
	
	### getting response and making cleaned up versions (for later comparisons)
	@$response1		= $_POST['Response'];
	$responseClean	= trim(strtolower($response1));
	$answerClean	= trim(strtolower($answer));
	$Acc			= NULL;
	
	
	#### Saving data into $_SESSION
	@$currentTrial['Response']['Response1']	= $_POST['Response'];
	@$currentTrial['Response']['RT']		= $_POST['RT'];
	@$currentTrial['Response']['RTkey']		= $_POST['RTkey'];
	@$currentTrial['Response']['RTlast']	= $_POST['RTlast'];
	## ADD ## if you've created a new inputname on trial.php you need to capture data here
	
	
	#### Calculating and saving accuracy for trials in  which this would be appropriate (excluding JOL and FreeRecall)
	if( ($trialType == 'test')	OR 	($trialType == 'testpic') OR
		($trialType == 'copy')	OR	($trialType == 'mcpic') ) {
		// determining similarity
		similar_text($responseClean, $answerClean, $Acc);
		$currentTrial['Response']['Accuracy'] = $Acc;
		// scoring and saving
		if($Acc == 100):
			$currentTrial['Response']['strictAcc'] = 1;
		else:
			$currentTrial['Response']['strictAcc'] = 0;
		endif;
		if($Acc >= 75):												## SET ## determines the % match required to count an answer as 1(correct) or 0(incorrect)
			$currentTrial['Response']['lenientAcc'] = 1;
		else:
			$currentTrial['Response']['lenientAcc'] = 0;
		endif;
	}
		
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Test/Presentation</title>
</head>
<?php flush(); ?>
<body>

<?php
	#### Tells the program which page to go to after postTrial
	// If you want to intercept the trial -> postTrial -> next loop simply change the $comingUp page
	$comingUp = 'next.php';
	
	#### trial timing code		## ADD ## tell program which timing to use for your new post-trial type
	if($postTrial == 'feedback'):
		$time = $_SESSION['FeedbackTime'];
	elseif ($postTrial == 'jol'):
		$time = $_SESSION['jolTime'];
	endif;
	if($_SESSION['Debug'] == TRUE) {	$time = 2;	}						// use this time for debugging  ## SET ##
	
	echo '<div id="Time" class="Hidden">' . $time . '</div>';				// hidden field that JQuery/JS uses to submit the trial to next.php
	
	// Classname tells the program whether to show user or computer timed version
	if($time == 'user'):
		$formName	= 'UserTiming';
		$formClass	= 'UserTiming';
	else:
		$formName	= 'ComputerTiming';
		$formClass	= 'ComputerTiming';
	endif;
	
	#### Showing feedback
	if($postTrial == 'feedback') {
		// picture trial version of feedback
		if($trialType == 'studypic' OR $trialType == 'testpic' OR $trialType == 'mcpic') {
			echo '<div class="FeedbackPic">
					<div class="gray">The correct answer is</div>
						<span>'	. show($cue)	. '</span>
						<div class="fbWord">
							'	. show($answer)	. '
						</div>';
			// Hidden form that collects RT and progresses trial to next.php
			echo '<form name="'.$formName.'" class="'.$formClass.'" autocomplete="off" action="'.$comingUp.'" method="post">
					<input class="RT Hidden" name="RT" type="text" value="RT" />
					<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  </form>';
			echo '</div>';
		}
		// version of feedback for everything else
		else {
			echo '<div class="Feedback">
					<div class="gray">The correct answer is</div>
					<span>' . show($cue).' : '.show($answer).'</span>';
			// Hidden form that collects RT and progresses trial to next.php
			echo '<form name="'.$formName.'" class="'.$formClass.'" autocomplete="off" action="'.$comingUp.'" method="post">
					<input	class="RT Hidden" name="RT" type="text"	value="RT" />
					<input	id="FormSubmitButton"	type="submit"	value="Done"	/>
				  </form>';
			echo '</div>';
		}
		
	}
	#### Showing JOL
	elseif ($postTrial == 'jol') {
		echo '<div id="JOLpos">';
		echo '<div id="jol">How likely are you to correctly remember this item on a later test?</div>
			  <div id="subpoint" class="gray">Type your response on a scale from 0-100 using the entire range of the scale</div>';
			
			echo '<form name="'.$formName.'" class="'.$formClass.'"  autocomplete="off"  action="'.$comingUp.'"  method="post">
					<input class="Textbox"		name="JOL"		type="text" value=""	autocomplete="off" /><br />
					<input class="RT Hidden"	name="RT"		type="text" value="RT" />
					<input class="RTkey Hidden" name="RTkey"	type="text" value="RTkey" />
					<input	id="FormSubmitButton"	type="submit"	value="Submit"	/>
				  </form>';
		echo '</div>';
	}
	## ADD ## put your own elseif here for a new post-trial type
	#### moving onto next trial
	else {
		echo '<meta http-equiv="refresh" content="0; url='.$comingUp.'">';
	}

?>
	<script	src="http://code.jquery.com/jquery-1.8.0.min.js"	type="text/javascript">	</script>
	<script	src="javascript/trial.js"				type="text/javascript">	</script>
</body>
</html>