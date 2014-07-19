<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
 
	// When using custom scoring, every scoring file must start with something
	// like the following 3 lines of code.
	// This is because in Login.php, the program will actually load each of
	// these scoring files, in order to find the columns (keys) needed in the
	// output file of this experiment.
	// So, first check for $findingKeys, and if that evaluates to true, return
	// an array of the columns needed to properly record all the data for this
	// trial.
	// This must be done, even if there are no new columns being added.
    if ($findingKeys) {
        return array('RT', 'LoopCount', 'Performance', 'Accuracy', 'RTkey', 'RTlast', 'strictAcc', 'lenientAcc');
    }
	
	// Also, the next line is super-important.  This is the line that actually
	// records data.  So, no matter what, make sure to put $_POST into $data.
    $data = $_POST;
?>