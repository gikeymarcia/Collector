<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 	$data= array();
    $keysNeeded = array('RT', 'Response1', 'RTkey', 'RTlast', 'strictAcc', 'lenientAcc');
	/*
	 * Q: Why are we using $data instead of setting values directly into $_SESSION['Trials']?
	 * A: $data holds all scoring information and once scoring is complete $data is merged into $_SESSION['Trials'][$currentPos]['Response']
	 *    Using $data as a middle man is needed becasue if scoring is happening on a `postTrial` then when merging $data back into $currentTrial['Respnonse']
	 *    the program will automatically prepend each stored key with 'post' (e.g., $data['RT'] would be merged as $data['postRT] iF scoring is happening for a postTrial)
	 */


 	#### Saving RT (true for all trials)
 	@$data['RT'] = $_POST['RT'];

 	#### Code that saves and scores information when user input is given
	if(isset($_POST['Response'])) {											// if there is a response given then do scoring

		#### Saving values specific to user input trials
		@$data['Response1']	= $_POST['Response'];
		@$data['RTkey']		= $_POST['RTkey'];
		@$data['RTlast']	= $_POST['RTlast'];

		### getting response and making cleaned up versions (for later comparisons)
		@$response1		= $_POST['Response'];
		$responseClean	= trim(strtolower($response1));
		$answerClean	= trim(strtolower($answer));
		$Acc			= NULL;

		#### Calculating and saving accuracy for trials with user input
		similar_text($responseClean, $answerClean, $Acc);					// determine text similarity and store as $Acc
		@$data['Accuracy'] = $Acc;

		#### Scoring and saving scores
		if($Acc == 100) {													// strict scoring
			@$data['strictAcc'] = 1;
		} else {
			@$data['strictAcc'] = 0;
		}

		if($Acc >= $lenientCriteria) {										// lenient scoring
			@$data['lenientAcc'] = 1;
		} else {
			@$data['lenientAcc'] = 0;
		}
	}
?>