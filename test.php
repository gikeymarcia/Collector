<?php
	ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
	session_start();										// start the session at the top of each page
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require("CustomFunctions.php");							// Loads all of my custom PHP functions
	
	
	// are we done with all presentations?  if so, send to done.php
	if (array_key_exists($_SESSION['Position'], $_SESSION['Trials']) == FALSE) {
		header("Location: FinalQuestions.php");
		exit;
	}
	
	
	// setting up easier to use and read aliases(shortcuts) of $_SESSION data
	$condition		=& $_SESSION['Condition'];
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=& $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Info']['Trial Type']));
		$item		=  trim(strtolower($currentTrial['Info']['Item']));
	
	
	// if we hit a *newfile* then the experiment is over (this means that we don't ask FinalQuestions until the last session of the experiment)
	if($item == '*newfile*') {
		header("Location: done.php");
		exit;
	}
	
	
	// if just coming from instructions then record that data into a file
	if(@$_POST['PrevTrial'] == "Instruction") {
		$instructFile = 'subjects/InstructionsData.txt';
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
	<title>Test/Presentation</title>
</head>

<body>
	
	
<?php
	// go to stepout page if this is a stepout trial
	if ($trialType == 'stepout') {
		$page = trim( $currentTrial['Info']['Order Notes'] );
		echo '<meta http-equiv="refresh" content="0; url='.$page.'">';
	}
	
	
	#### determine trial timing
	// if trial timing is a number then use that and assume computer timed. else use login.php parameter values
	$timingReported =& trim(strtolower($currentTrial['Info']['Timing']));
	if(is_numeric($timingReported)) {
		$time = $timingReported;
	}
	elseif ($timingReported == 'computer') {
		## ADD ## you'll need to tell the program which default 'computer' timing to use for your new trial
		if( ($trialType == 'study') OR ($trialType == 'studypic') OR ($trialType == 'instruct') ) {
			$time = $_SESSION['StudyTime'];
		}
		elseif( ($trialType == 'test') OR ($trialType == 'testpic') OR
				($trialType == 'copy') OR ($trialType == 'mcpic') ) {
			$time = $_SESSION['TestTime'];
		}
		elseif ($trialType == 'passage') {
			$time = $_SESSION['PassageTime'];
		}
		elseif ($trialType == 'freerecall') {
			$time = $_SESSION['FreeRecallTime'];
		}
		elseif ($trialType == 'jol') {
			$time = $_SESSION['jolTime'];
		}
	} else {
		$time = 'user';
	}
	if($_SESSION['Debug'] == TRUE) {
		$time = 2;					## SET ## if debug mode is on all trials will be this many seconds long
	}
	// hidden field that JQuery/JS uses to submit the trial to postTrial.php
	echo '<div id="Time" class="Hidden">' . $time . '</div>';
		
	// changing form classname based on user or computer timing.  I use the classname to do JQuerty magic
	if($time == 'user'):
		$formName	= 'UserTiming';
		$formClass	= 'UserTiming';
	else:
		$formName	= 'ComputerTiming';
		$formClass	= 'ComputerTiming';
	endif;
	
	
	#### Presenting different trial types ####
	// trials without any user input
	## ADD ## if your trial has no input then add it to the if line below
	if( ($trialType == 'study') OR ($trialType == 'studypic') OR ($trialType == 'passage') ) {
		// Study trial type
		if ($trialType == 'study') {
			echo '<div class="WordWrap PreCache">
					<span class="left">'.$cue.'</span>
					<span class="divider">:</span>
					<span class="right">'.$target.'</span>
				  </div>';
		}
		// StudyPic trial type
		elseif ($trialType == 'studypic') {
			echo '<div class="pic PreCache">
					'. show($cue).
				 '</div>';
			
			echo '<div class="picWord PreCache">'.$target.'</div>';
			$formClass = $formClass.' center';
		}
		// Passage trial type
		elseif($trialType == 'passage') {
			echo '<div class="passage PreCache">'.fixBadChars($cue).'</div>
				  <div id="end">End of Passage</div>';
				  $formClass = $formClass.' center';
		}
		// Instruct trial type
		elseif ($trialType == 'instruct') {
			echo '<div id="centerContent PreCache">
					<div class="instruct">'. $_SESSION['Trials'][$currentPos]['Info']['Order Notes'].'</div>
				  </div>';
		}
		## ADD ## your new trial should be an elseif here (if it has no user input)
		echo '<div id="buttPos">';
		// give the form a different name for user and comuputer timed
		// I use formname + JQuery to hide the submit button when the form is a computer timed form
		echo '<form name="'.$formName.'" class="'.$formClass.'" action="postTrial.php" method="post">
				<input	name="RT"	type="text"		value=""	class="RT Hidden"	/>
				<input	id="FormSubmitButton"	type="submit"	class="Hidden"	value="Done"	/>
			  </form>';
		echo '</div>';
	}
	
	
	// trials with user input
	## ADD ## if your trial has input then add it to the if line below
	if(	($trialType == 'test')	OR	($trialType == 'testpic')	OR
		($trialType == 'copy')	OR	($trialType == 'freerecall')OR
		($trialType == 'jol')	OR	($trialType == 'mcpic')) {
		// Test trial type
		if ($trialType == "test") {
			echo '<div class="WordWrap">
					<span class="leftcopy PreCache">'.$cue.'</span>
					<span class="dividercopy">:</span>
					<form name="'.$formName.'" class="'.$formClass.' leftfloat PreCache" action="postTrial.php" method="post">
						<input	name="Response"	type="text"	value=""		class="Textbox Right PreCache"	/>
						<input	name="RT"		type="text"	value="RT"		class="RT Hidden"		/>
						<input	name="RTkey"	type="text"	value="RTkey"	class="RTkey Hidden" 	/>
						<input	name="RTlast"	type="text"	value="RT"		class="RTlast Hidden" 	/>
						<input	id="FormSubmitButton"	type="submit"	class="Hidden"	value="Submit"	/>
					</form>
				  </div>';
		}
		// TestPic trial type
		elseif($trialType == 'testpic') {
			echo '<div class="pic PreCache">
					'. show($cue).
				 '</div>';
			$formClass = $formClass.' center';
			
			echo '<form name="'.$formName.'" class="'.$formClass.'" action="postTrial.php" method="post">
					<input  name="Response" type="text" value=""		class="Textbox picWord PreCache" />	<br />
					<input	name="RT"		type="text"	value="RT"		class="RT Hidden"		/>
					<input	name="RTkey"	type="text"	value="RTkey"	class="RTkey Hidden" 	/>
					<input	name="RTlast"	type="text"	value="RT"		class="RTlast Hidden" 	/>
					<input	id="FormSubmitButton"	type="submit"	class="Hidden"	value="Submit"	/>
				  </form>';
		}
		
		// MCpic trial type
		elseif($trialType == 'mcpic') {
			// show the image
			echo '<div class="pic PreCache">
					'. show($cue).
				 '</div>';		
			## SET ## If you're going to use MCpic trials then you should change the category names in $MCbuttons
			$MCbuttons = array( "Hawkins", "Cat2", "Cat3", "Cat4", "Cat6", "Cat6", "Cat7", "Ocean Fish", "Cat9", "Cat10", "Cat11", "Cloud Fish");
			
			if($_SESSION['MCbutton'] == FALSE) {
				shuffle($MCbuttons);							// turn this line off to maintain the same choice order between-subjects
				$_SESSION['MCbutton'] = $MCbuttons;
			}
			$itemsPerRow	= 4;								## SET ## names says it all (use values 1-4; anything bigger causes problems which require css changes)
			$count			= 0;
			// display the MC button choices
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
			echo '<form name="'.$formName.'" class="'.$formClass.'" action="postTrial.php" method="post">
					<input	name="Response"	type="text"	value=""	class="Textbox Hidden"	/>	<br />
					<input	name="RT"		type="text"	value="RT"	class="RT Hidden"		/>
				  </form>';
		}
		// Copy trial type
		elseif($trialType == 'copy') {
			echo '<div class="WordWrap PreCache">
					<span class="left">'.$cue.'</span>
					<span class="divider">:</span>
					<span class="right">'.$target.'</span>
				  </div>
				  
				  <div class="WordWrap PreCache">
				  	<span class="leftcopy">'.$cue.'</span>
				  	<span class="dividercopy">:</span>
				  	<form name="'.$formName.'" class="'.$formClass.' leftfloat" action="postTrial.php" method="post">
					  	<input 	name="Response" type="text" value=""		class="Textbox"			/>
					  	<input	name="RT"		type="text"	value="RT"		class="RT Hidden"		/>
						<input	name="RTkey"	type="text"	value="RTkey"	class="RTkey Hidden" 	/>
						<input	name="RTlast"	type="text"	value="RT"		class="RTlast Hidden" 	/>
						<input	id="FormSubmitButton"	type="submit"	class="Hidden"	value="Submit"	/>
					  </form>
				  </div>';
		}
		// FreeRecall trial type
		elseif($trialType == 'freerecall') {
			$prompt =& $_SESSION['Trials'][$currentPos]['Info']['Order Notes'];
			echo '<div id="centerContent">
				<div class="Prompt PreCache">' . $prompt . '</div>
					<form name="'.$formName.'" class="'.$formClass.'" action="postTrial.php" method="post">
						<textarea rows="20" cols="60" name="Response" class="PreCache" wrap="physical" value=""></textarea>	<br />
						<input	name="RT"		type="text"	value="RT"		class="RT Hidden"		/>
						<input	name="RTkey"	type="text"	value="RTkey"	class="RTkey Hidden" 	/>
						<input	name="RTlast"	type="text"	value="RT"		class="RTlast Hidden" 	/>
						<input	id="FormSubmitButton"	type="submit"	class="Hidden"	value="Submit"	/>
					</form>
					</div>';
		}
		// JOL trial type
		elseif($trialType == 'jol') {
			echo '<div id="jol">How likely are you to correctly remember this item on a later test?</div>
					<div id="subpoint" class="gray">Type your response on a scale from 0-100 using the entire range of the scale</div>';
			
			echo '<form name="'.$formName.'" class="'.$formClass.'" action="postTrial.php" method="post">
					<input	name="Response"	type="text"	value=""		class="Textbox"			/>	<br />
					<input	name="RT"		type="text"	value="RT"		class="RT Hidden"		/>
					<input	name="RTkey"	type="text"	value="RTkey"	class="RTkey Hidden" 	/>
					<input	name="RTlast"	type="text"	value="RT"		class="RTlast Hidden" 	/>
					<input	id="FormSubmitButton"	type="submit"	class="Hidden"	value="Submit"	/>
				  </form>';
		}
		## ADD ## your new trial should be an elseif here (if it has user input)
	}
	
	
	#### Pre-Cache Next trial ####
	echo '<div class="Hidden">';
			echo show($nextTrial['Stimuli']['Cue']).'<br />';
			echo show($nextTrial['Stimuli']['Target']).'<br />';
			echo show($nextTrial['Stimuli']['Answer']).'<br />';
	echo '</div>';
	
	
	
	#### Diagnostics ####		un comment these to get tons of info about each trial
	// echo "<div>";
		// echo "Condition #: {$_SESSION['Condition']['Number']} <br />";
		// echo "Condition Stim File: {$_SESSION['Condition']['Stimuli']} <br />";
		// echo "Condition Order File: {$_SESSION['Condition']['Order']} <br />";
		// echo "Condition description: {$_SESSION['Condition']['Condition Description']} <br />";
	// echo "</div>";
	// echo "<br />";
	// echo '<div class="Trial">';
		// echo "Trial Number: {$currentPos} <br />";
		// echo "Trial Type: {$trialType}<br />";
		// echo "Post Trial: {$currentTrial['Info']['Post Trial']} <br />";
		// echo "Trial timing: {$currentTrial['Info']['Timing']} <br />";
		// echo "Trial Time (seconds): {$time}";
		// echo "<br />";
	// echo '</div>';
	// echo '<div>
			// cue: '.show($cue).'<br />
			// target: '.show($target).'<br />
			// answer: '.show($answer).'<br />
		// </div>';
	// readable($currentTrial, "Current trial");
	#### Diagnostics ####
?>
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
	<script src="javascript/test.js" type="text/javascript"> </script>
</body>
</html>