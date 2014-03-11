<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 	
 	#### Saving RT (true for all trials)
 	@$currentTrial['Response']['RT'] = $_POST['RT'];
 	
 	#### Code that saves and scores information when user input is given
	if(isset($_POST['Response'])) {											// if there is a response given then do scoring
		
		#### Saving values specific to user input trials
		@$currentTrial['Response']['Response1']	= $_POST['Response'];
		@$currentTrial['Response']['RTkey']		= $_POST['RTkey'];
		@$currentTrial['Response']['RTlast']	= $_POST['RTlast'];
		
		### getting response and making cleaned up versions (for later comparisons)
		@$response1		= $_POST['Response'];
		$responseClean	= trim(strtolower($response1));
		$answerClean	= trim(strtolower($answer));
		$Acc			= NULL;
		
		#### Calculating and saving accuracy for trials with user input
		similar_text($responseClean, $answerClean, $Acc);					// determine text similarity and store as $Acc
		$currentTrial['Response']['Accuracy'] = $Acc;
		
		#### Scoring and saving scores
		if($Acc == 100) {													// strict scoring
			$currentTrial['Response']['strictAcc'] = 1;
		} else {
			$currentTrial['Response']['strictAcc'] = 0;
		}
		
		if($Acc >= $lenientCriteria) {										// lenient scoring
			$currentTrial['Response']['lenientAcc'] = 1;
		} else {
			$currentTrial['Response']['lenientAcc'] = 0;
		}
		
	}
		
?>