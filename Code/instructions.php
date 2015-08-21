<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */

    ####  good place to pull in values and/or compute things that'll be inserted into the HTML below
    require 'initiateCollector.php';
	
    $title = 'Experiment Instructions';
	$_dataController = 'instructions';
    require $_PATH->get('Header');
?>
<form name="Login" id="content" action="InstructionsRecord.php" method="post">
    <div class="alert alert-instructions">Please carefully read the instructions again.</div>
    <?php include FileExists($_PATH->get('Instructions')); ?>
    
	<input  name="RT"        id="RT"    type="hidden" value="0" />
	<input  name="Fails"     id="Fails" type="hidden" value="0" />
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
</form>

<?php
    require $_PATH->get('Footer');
