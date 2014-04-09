<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	require 'CustomFunctions.php';							// Load custom PHP functions
	initiateCollector();

	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
	// pulls in the specified tetris time from the order file, column "Timing"
	$time			=  trim($currentTrial['Procedure']['Timing']);
	if($_SESSION['Debug'] == TRUE) {	$time = 2;	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<title>Tetris</title>
</head>
<?php flush(); ?>
<body data-controller=trial data-action=stepout>
	<div class=stepout-clock>
	    <span>Seconds remaining</span>
		<h3 class=countdown></h3>
	</div>

	<div class='tetris-wrap textcenter'>
        <h3>Play a quick game of Tetris while we load the next part</h3>
        <div class='grid tetris-controls'>
            <div class='grid-item'>
                <h2>Controls</h2>
            </div><!--
         --><div class='grid-item grid-1-3'>
                <p>Move:</p>
            </div><!--
         --><div class='grid-item grid-2-3'>
                <p><strong>Left</strong> and <strong>Right arrows</strong></p>
            </div><!--
         --><div class='grid-item grid-1-3'>
                <p>Rotate Right:</p>
            </div><!--
         --><div class='grid-item grid-2-3'>
                <p><strong>X</strong> or <strong>Up arrow</strong></p>
            </div><!--
         --><div class='grid-item grid-1-3'>
                <p>Rotate Left:</p>
            </div><!--
         --><div class='grid-item grid-2-3'>
                <p><strong>Z</strong></p>
            </div><!--
         --><div class='grid-item grid-1-3'>
                <p>Drop:</p>
            </div><!--
         --><div class='grid-item grid-2-3'>
                <p><strong>Spacebar</strong></p>
            </div>
        </div>
        <div class=button id=reveal>Start</div>
        <div class=tetris>
            <embed src="http://www.cogfog.com/nblox.swf" menu=false width=550 height=650
            quality=high type="application/x-shockwave-flash"
            pluginspage="http://www.macromedia.com/go/getflashplayer/" />
        </div>
	</div>

    <!-- used to set timer -->
	<div id=Time class=hidden><?php echo $time; ?></div>

	<!-- hidden form to advance page -->
	<form class=hidden action="postTrial.php" method=get></form>

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