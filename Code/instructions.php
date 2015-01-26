<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */

    ####  good place to pull in values and/or compute things that'll be inserted into the HTML below
    require 'initiateCollector.php';
	
    $title = 'Experiment Instructions';
	$_dataController = 'instructions';
    require $_codeF . 'Header.php';
?>

	<?php include FileExists($up . $expFiles . $instructionsFileName); ?>

	<form class="hidden" name="Login" action="InstructionsRecord.php" method="post">
		<input  name="RT"        id="RT"    type="text" value="0" />
		<input  name="Fails"     id="Fails" type="text" value="0" />
		<input  id="FormSubmitButton" type="submit" />
	</form>

    <div class="precache">
    <?php
    ### PRE-CACHES All cues and answers used in experiment ####
    foreach ($_SESSION['Trials'] as $Trial) {
        echo show($Trial['Stimuli']['Cue'])    . ' ';
        echo show($Trial['Stimuli']['Answer']) . ' ';
        echo '<br />';
    }
    ?>
    </div>
    <div class="alert alert-instructions">Please carefully read the instructions again.</div>

<?php
    require $_codeF . 'Footer.php';