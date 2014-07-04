<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */

    require 'CustomFunctions.php';                          // Load custom PHP functions
    initiateCollector();
	require 'fileLocations.php';			// sends file to the right place
	require $up.$expFiles.'Settings.php';	// experiment variables


	// setting up easier to use and read aliases(shortcuts) of $_SESSION data
	$condition		=& $_SESSION['Condition'];
	$currentPos		=& $_SESSION['Position'];
	$currentPost 	=& $_SESSION['PostNumber'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
		$cue		=& $currentTrial['Stimuli']['Cue'];
		$target		=& $currentTrial['Stimuli']['Target'];
		$answer		=& $currentTrial['Stimuli']['Answer'];
	
	// this will also create aliases of any columns that apply to the current trial (filtering out "post X" prefixes when necessary)
	// currentProcedure becomes an array of all columns matched for this trial, using their original column names
	$currentProcedure = ExtractTrial( $currentTrial['Procedure'], $currentPost );
	if( !isset( $item ) ) $item = $currentTrial['Procedure']['Item'];
	
	// Whenever Trial.php finds $_POST data, it will try to store that data
	// immediately, rather than simply holding it through the trial
	//
	// This is done by either storing the data in $currentTrial['Response'],
	// or by redirecting to next.php, where the data is actually recorded
	// into a file.
	//
	// Data from instructions.php is detected by $currentPost === -1
	if( $_POST !== array() ) {
		if( $currentPos === 1 AND $currentPost === -1 ) {
			// posting from instructions.php
			// $currentPost was set to -1 at login.php, but it will only ever be set to 0 in the future, so this only happens at the beginning
			$currentPost = 0;
			$data = array(
				 'Username' => $_SESSION['Username']
				,'ID' 		=> $_SESSION['ID']
				,'Date' 	=> date('c') 
			);
			$data += $_POST;
			arrayToLine( $data, $instructPath );
			header('Location: trial.php');
			exit;
		} else {
			$trialType = strtolower(trim( $trialType ));
			if( $currentPost === 0 ) {
				$keyMod = '';
			} else {
				$keyMod = 'post'.$currentPost.'_';
			}
			require $_SESSION['Trial Types'][$trialType]['scoring'];
			#### merging $data into $currentTrial['Response]
			$currentTrial['Response'] = placeData($data, $currentTrial['Response'], $keyMod);
			
			$next = 'trial.php';
			$notTrials = array_flip( array( 'off', 'no', '', 'n/a' ) );
			// Now we need to find the current trial type.
			while( TRUE ) {
				++$currentPost;
				unset( $trialType );	// so we can extract a new one
				$currentProcedure = ExtractTrial( $currentTrial['Procedure'], $currentPost );
				if( count($currentProcedure) === 0 ) {
					$next = 'next.php';
					break;
				}
				$trialType = strtolower(trim( $trialType ));
				if( isset( $_SESSION['Trial Types'][$trialType] ) ) {
					$next = 'trial.php';
					break;
				}
			}
			
			header('Location: '.$next);
			exit;
		}
	}


	// if we hit a *newfile* then the experiment is over (this means that we don't ask FinalQuestions until the last session of the experiment)
	if($item == '*newfile*') {
		header("Location: done.php");
		exit;
	}
	
	if( $currentPost === 0 ) {
		$trialType = trim(strtolower($currentTrial['Procedure']['Trial Type']));
	} elseif( $currentPost === 1 AND isset( $currentTrial['Procedure']['Post Trial'] ) ) {
		$trialType = trim(strtolower($currentTrial['Procedure']['Post Trial']));
	} elseif( isset( $currentTrial['Procedure'][ 'Trial Type '.$currentPost ] ) ) {
		$trialType = trim(strtolower($currentTrial['Procedure'][ 'Post Trial '.$currentPost ]));
	}


	// if there is another item coming up then set it as $nextTrial
	if(array_key_exists($currentPos+1, $_SESSION['Trials'])) {
		$nextTrial =& $_SESSION['Trials'][$currentPos + 1];
	} else { $nextTrial = FALSE;}
	
		$data = array( 
			 'Username' => $_SESSION['Username']
			,'ID' 		=> $_SESSION['ID']
			,'Date' 	=> date('c') 
		);
		$data += $_POST;
		arrayToLine( $data, $instructPath);
	// this only happens once, so that refreshing the page doesn't do anything, and reaching next.php is the only way to update the timestamp
	if( !isset($_SESSION['Timestamp']) ) {
		$_SESSION['Timestamp'] = microtime(TRUE);
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<title>Trial</title>
</head>
<?php flush();	?>
<body data-controller=trial data-action=<?php echo $trialType; ?>>

<?php
	// variables I'll need and/or set in trialTiming() function
	$timingReported = strtolower(trim( $timing ));
	$formClass	= '';
	$time		= '';
	if( !isset( $minTime ) ) {
		$minTime	= 'not set';
	}

	#### Presenting different trial types ####
	$expFiles  = $up.$expFiles;							// setting relative path to experiments folder for trials launched from this page
    $postTo    = 'trial.php';
	$trialFail = FALSE;									// this will be used to show diagnostic information when a specific trial isn't working
	$trialFile = FileExists($trialF.$trialType);
?>

<!-- User visible HTML markup in here -->
<div class=cframe-outer>
    <div class=cframe-inner>
        <div class=cframe-content>
            <!-- trial content -->
            <?php
                if ($trialFile):
                   	include $trialFile;
                else: ?>
            		<h2>Could not find the following trial type: <strong><?php echo $trialType; ?></strong></h2>
            		<p>Check your procedure file to make sure everything is in order. All information about this trial is displayed below.</p>

            		<!-- default trial is always user timing so you can click 'Done' and progress through the experiment -->
            		<div class=precache>
            			<form name=UserTiming class=UserTiming action="<?php echo $postTo; ?>" method=post>
            				<input class=hidden id=RT name=RT type=text value=""  />
            				<input class=button id=FormSubmitButton type=submit value="Done" />
            			</form>
            		</div>
            <?php
                $trialFail = TRUE;
                $time = 'user';
                endif;
            ?>

            <!-- hidden field that JQuery/JavaScript uses to check the timing to $postTo -->
            <div id=Time class=hidden><?php echo $time; ?></div>
            <div id=minTime class=hidden><?php echo $minTime; ?></div>

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

<!-- Pre-Cache Next trial -->
<div class=precachenext>
    <?php
    echo show($nextTrial['Stimuli']['Cue']).'   <br />';
    echo show($nextTrial['Stimuli']['Target']).'<br />';
    echo show($nextTrial['Stimuli']['Answer']).'<br />';
    ?>
</div>

<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="javascript/collector_1.0.0.js" type="text/javascript"></script>

</body>
</html>