<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */

    ####  good place to pull in values and/or compute things that'll be inserted into the HTML below
    require 'fileLocations.php';                    // sends file to the right place
    require $up . $expFiles . 'Settings.php';       // experiment variables
    require 'CustomFunctions.php';                  // Loads all of my custom PHP functions
    // Load custom PHP functions
    initiateCollector();
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="css/global.css" rel="stylesheet" type="text/css" />
    <title>Experiment Instructions</title>
</head>

<body data-controller="instructions">
    <div class="cframe-outer">
        <div class="cframe-inner">

            <?php include FileExists($up . $expFiles . $instructionsFileName); ?>

            <form class="hidden" name="Login" action="<?php echo $up . $codeF; ?>trial.php" method="post">
                <input  name="RT"        id="RT"    type="text" value="0" />
                <input  name="Fails"     id="Fails" type="text" value="0" />
                <input  id="FormSubmitButton" type="submit" />
            </form>

        </div>
    </div>

    <div class="alert alert-instructions">Please carefully read the instructions again.</div>

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

<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="javascript/collector_1.0.0.js" type="text/javascript"></script>
