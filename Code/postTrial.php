<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	require 'fileLocations.php';							// sends file to the right place
	require $up.$expFiles.'Settings.php';					// experiment variables
	require 'CustomFunctions.php';							// Load custom PHP functions

	initiateCollector();

	#### setting up aliases (for later use)
	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=  $currentTrial['Stimuli']['Answer'];
		$trialType	=  trim(strtolower($currentTrial['Procedure']['Trial Type']));
		$postTrial	=  trim(strtolower($currentTrial['Procedure']['Post Trial']));

	$customScoring = FALSE;
	// later there will be code to denote when custom scoring should occur

	// use default scoring scheme if alternative scoring is not denoted
	if ($customScoring == FALSE) {
		$scoringFile = FileExists($scoring);
		require $scoringFile;
	}

	#### merging $data into $currentTrial['Response]
	$currentTrial['Response'] = placeData($data, $currentTrial['Response']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>PostTrial</title>
</head>

<body>
<?php
	flush();

	#### Tells the program which page to go to after postTrial
+   // If you want to intercept the trial -> postTrial -> next loop simply change the $postTo page
 +  $postTo = 'next.php';

	#### trial timing code		## ADD ## tell program which timing to use for your new post-trial type
	if($postTrial == 'feedback'):
		$time = $feedbackTime;
	elseif ($postTrial == 'jol'):
		$time = $jolTime;
	endif;
	if($_SESSION['Debug'] == TRUE) {	$time = $debugTime;	}

	echo '<div id="Time" class="hidden">' . $time . '</div>';				// hidden field that JQuery/JS uses to submit the trial to next.php

	// Classname tells the program whether to show user or computer timed version
	if($time == 'user'):
		$formName	= 'UserTiming';
		$formClass	= 'UserTiming';
	else:
		$formName	= 'ComputerTiming';
		$formClass	= 'ComputerTiming';
	endif;
?>

<div class=cframe-outer>
    <div class=cframe-inner>
        <div class=cframe-content>
        <?php
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
        			echo '<form name="'.$formName.'" class="'.$formClass.'" autocomplete="off" action="'.$postTo.'" method="post">
        					<input class=hidden id=RT  name=RT type=text value="" />
        					<input class=button id=FormSubmitButton  type=submit value="Done"	/>
        				  </form>';
        			echo '</div>';
        		}
        		// version of feedback for everything else
        		else {
        			echo '<h2>The correct answer was:</h2>
                          <div class=study>
                              <span class=study-left>'  .$cue.  '</span>
                              <span class=study-divider>   :     </span>
                              <span class=study-right>'.$target.'</span>
                          </div>';

                    // Hidden form that collects RT and progresses trial to next.php
                    echo '<form name="'.$formName.'" class="'.$formClass.' textcenter" autocomplete="off" action="'.$postTo.'" method="post">
                              <input class=hidden id=RT  name=RT type=text value="" />
                              <input class="button button-trial-advance" id=FormSubmitButton  type=submit value="Done"   />
                          </form>';
        		}

        	}
	#### Showing JOL
	elseif ($postTrial == 'jol') {
        echo "<div class=textcenter>
                <h3>How likely are you to correctly recall this item on a later test?</h3>
                <p>Type your response on a scale from 0-100.</p>
                <br />
            </div>

            <form class='".$formClass." collector-form textcenter'  autocomplete=off  action='".$postTo."'  method=post>
                <input  name=Response type=text value='' autocomplete=off />
                <input class=hidden  id=RT     name=RT       type=text value='RT'       />
                <input class=hidden  id=RTkey  name=RTkey    type=text value='no press' />
                <input class=hidden  id=RTlast name=RTlast   type=text value='no press' />
                <input class=button id=FormSubmitButton type=submit value='Submit'   />
            </form>";
	}

	## ADD ## put your own elseif here for a new post-trial type
	#### moving onto next trial
	else {
		echo '<meta http-equiv="refresh" content="0; url='.$postTo.'">';
	}

?>
	        <br>
    		<div id="showTimer" class="hidden">
    			<div> Start (ms):	<span id="start">	</span>	</div>
    			<div> Current (ms):	<span id="current">	</span>	</div>
    			<div> Timer (ms):	<span id="dif">		</span>	</div>
    		</div>
		</div>
    </div>
</div>

<script	src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
<script	src="javascript/collector_1.0.0.js"	type="text/javascript"></script>

</body>
</html>