<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */

	require 'fileLocations.php';			// sends file to the right place
	require $up.$expFiles.'Settings.php';	// experiment variables
	require 'CustomFunctions.php';			// Loads all of my custom PHP functions
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
	$expFiles  = $up.$expFiles;							// setting relative path to experiments folder for trials launched from this page
	$trialFail = FALSE;									// this will be used to show diagnostic information when a specific trial isn't working
	$trialFile = FileExists($trialF.$trialType);
?>


<div class=cframe-outer>
    <div class=cframe-inner>
        <div class=cframe-content>
            <!-- user visible HTML markup -->
            <?php
                if ($trialFile):
                   	include $trialFile;
                else: ?>
            		<h2>Could not find the following trial type: <strong><?php echo $trialType; ?></strong></h2>
            		<p>Check your procedure file to make sure everything is in order. All information about this trial is dispalyed below.</p>';

            		<!-- default trial is always user timing so you can click 'Done' and progress through the experiment -->
            		<div class=precache>
            			<form name=UserTiming class=UserTiming action="postTrial.php" method=post>
            				<input class=hidden id=RT name=RT type=text value=""  />
            				<input class=button type=submit value="Done" />
            			</form>
            		</div>
            <?php
                $trialFail = TRUE;
                $time = 'user';
                endif;
            ?>

            <!-- placeholders for a debug function that shows timer values -->
            <div id=showTimer class=hidden>
                <div> Start (ms):   <span id=start>   </span> </div>
                <div> Current (ms): <span id=current> </span> </div>
                <div> Timer (ms):   <span id=dif>     </span> </div>
            </div>

             <?php
                #### Diagnostics ####
                if ($trialDiagnostics == TRUE OR $trialFail == TRUE) {
                    // clean the arrays used so that they output strings, not code
                    $clean_session      = arrayCleaner($_SESSION);
                    $clean_currentTrial = arrayCleaner($currentTrial);
                    echo "<div class=diagnostics>
                            <h2>Diagnostic information</h2>
                            <ul>
                                <li> Condition #:           {$clean_session['Condition']['Number']}                </li>
                                <li> Condition Stim File:   {$clean_session['Condition']['Stimuli']}               </li>
                                <li> Condition Order File:  {$clean_session['Condition']['Procedure']}             </li>
                                <li> Condition description: {$clean_session['Condition']['Condition Description']} </li>
                            </ul>
                            <ul>
                                <li> Trial Number:          {$currentPos}                                          </li>
                                <li> Trial Type:            {$trialType}                                           </li>
                                <li> Post Trial:            {$clean_currentTrial['Procedure']['Post Trial']}       </li>
                                <li> Trial timing:          {$clean_currentTrial['Procedure']['Timing']}           </li>
                                <li> Trial Time (seconds):  {$time}                                                </li>
                            </ul>
                            <ul>
                                <li> Cue: ".                show($cue)."                                        </li>
                                <li> Target:".              show($target)."                                     </li>
                                <li> Answer:".              show($answer)."                                     </li>
                            </ul>";
                    readable($currentTrial, "Information loaded about the current trial");
                    readable($_SESSION['Trials'], "Information loaded about the entire experiment");
                    echo "</div>";
                }
            ?>
        </div>
    </div>
</div>

<!-- hidden field that JQuery/JavaScript uses to check the timing to postTrial.php -->
<div id=Time class=hidden><?php echo $time; ?></div>
<div id=minTime class=hidden><?php echo $minTime; ?></div>

<!-- Pre-Cache Next trial -->
<div class=precachenext>
    <?php
    echo show($nextTrial['Stimuli']['Cue']).'   <br />';
    echo show($nextTrial['Stimuli']['Target']).'<br />';
    echo show($nextTrial['Stimuli']['Answer']).'<br />';
    ?>
</div>

<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
<script src="javascript/trial.js" type="text/javascript"> </script>

</body>
</html>