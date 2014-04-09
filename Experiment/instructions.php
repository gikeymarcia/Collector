<?php
/*	Collector
 A program for running experiments on the web
 Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */

####  good place to pull in values and/or compute things that'll be inserted into the HTML below
require '../Code/fileLocations.php';
// sends file to the right place
require $up . $codeF . 'CustomFunctions.php';
// Load custom PHP functions
initiateCollector();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="<?php echo $up.$codeF ?>css/global.css" rel="stylesheet" type="text/css" />
	<title>Experiment Instructions</title>
</head>

<body data-controller=instructions>
	<div class=cframe-outer>
        <div class=cframe-inner>
            <div class=cframe-content>
                <h2 class=textcenter>Task Instructions</h2>
                <!-- ## SET ## Change the instructions text to match your task. You start and end new paragraphs with <p>paragraph here</p>-->
                <p> In this study you will be studying some stuff then you will need to recall that stuff.
                    After each bunch of stuff there will be some kind of memory task.</p>
                <p> Please pay close attention to the things we are showing you.</p>
                <p> As many paragraphs as you would like can go here.  Instructions are done.  Time for you to move onto the experiment</p>

                <div class=textcenter id=revealRC>
                    <button class=button>Advance</button>
                </div>
            </div>

            <!-- ## SET ## This ensures that they read your instructions.  Participants must correctly answer something about the procedure -->
            <div class=cframe-content>
                <div class=readcheck>                Should you pay close attention?  (hint: Answer is in the instructions)
                    <ol>
                        <li class="MCbutton wrong"    > I don't think so </li>
                        <li class="MCbutton wrong"    > Nope             </li>
                        <li class=MCbutton id=correct > Yes              </li>
                        <li class="MCbutton wrong"    > I can't read.    </li>
                    </ol>
                </div>
            </div>


            <form class=hidden name=Login action="<?php echo $up . $codeF; ?>trial.php" method=post>
                <input  name=RT        id=RT    type=text value=0 />
                <input  name=Fails     id=Fails type=text value=0 />
                <input  name=PrevTrial type=text value=Instruction />
                <input  id=FormSubmitButton type=submit />
            </form>

        </div>
    </div>

    <div class="alert alert-instructions">Please carefully read the instructions again.</div>

    <div class=precache>
    foreach ($_SESSION['Trials'] as $Trial) {
        echo show($Trial['Stimuli']['Cue']) . ' ';
        echo show($Trial['Stimuli']['Target']) . ' ';
        echo show($Trial['Stimuli']['Answer']) . ' ';
        echo '<br />';
    }
    ?>
    </div>

<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"></script>
<script src="<?php echo $up.$codeF;?>javascript/collector_1.0.0.js" type="text/javascript"></script>
