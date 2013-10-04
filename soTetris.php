<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2013 Mikey Garcia & Nate Kornell
 */
	require("CustomFunctions.php");							// Load custom PHP functions
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
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<link href='http://fonts.googleapis.com/css?family=Orbitron:700' rel='stylesheet' type='text/css'>
	<title>Tetris</title>
</head>
<?php flush(); ?>
<body>
	<div id="Clock"><span>Seconds remaining</span>
		<div class="Countdown"></div>
	</div>
	
	<h1 class="stepout">Tetris!</h1>
	
	<div id="StepoutInstructions">
			<p>Try to achieve the highest score possible</p>
			<p>Move the pieces from side to side using the <b>left</b> and <b>right arrows</b>.<br />
				Press the <b>X</b> or the <b>Up arrow</b> to rotate right; press <b>Z</b> to rotate left. <br />
				Drop the piece into place with the <b>space bar</b></p>
	</div>
	
	
	<div id="Tetris">
		<EMBED src="http://www.cogfog.com/nblox.swf" menu="false" width="550" height="650"
		quality="high" TYPE="application/x-shockwave-flash" 
		PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer/" />
	</div>
			
	
	<?php
		echo '<div id="time" class="Hidden">'.$time.'</div>';
		
		// this hidden and empty form is submitted to progress the page
		echo '<form id="loadingForm" action="postTrial.php" method="get"> </form>';
		
		#### Pre-cache next trial
		echo '<div class="Hidden">';
			echo show($_SESSION['Trials'][$_SESSION['Position']+1]['Stimuli']['Cue']);
			echo show($_SESSION['Trials'][$_SESSION['Position']+1]['Stimuli']['Target']);
			echo show($_SESSION['Trials'][$_SESSION['Position']+1]['Stimuli']['Answer']);
		echo '</div>';
	?>
	
	<script src="javascript/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<script type="text/javascript">
		var timer = $("#time").html();
		var interval = 1000;
		$(".Countdown").html(timer);
		
		setInterval(countdown,interval);
		
		function countdown() {
			timer = timer-1;
			if(timer >= 0) {
				$(".Countdown").html(timer);
			}
			if(timer == 5) {
				// hide these things when timer hits 5 seconds
				$("EMBED").addClass("Hidden");
				$("#Clock").addClass("Hidden");
				$("#StepoutInstructions").addClass("Hidden");
				$("h1").addClass("Hidden");
				$("#Tetris").html("<div class=\"cont\">Get ready to continue in ...  <div class=\"Countdown\">5</div></div>");
			}
			if(timer <= 0) {
				$('#loadingForm').submit();
			}
		}
	</script>

</body>
</html>