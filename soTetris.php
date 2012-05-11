<?php
// fixes problems reading files saved on mac
ini_set('auto_detect_line_endings', true);
// start the session at the top of each page
session_start();
if ($_SESSION['Debug'] == FALSE) {
	error_reporting(0);
}
// Loads all of my custom PHP functions
require("CustomFunctions.php");

$currentPos =& $_SESSION['Position'];
$currentTrial =& $_SESSION['Trials'][$currentPos];
// pulls in the specified tetris time from the order file, column "Timing"
$time = trim($currentTrial['Info']['Timing']);
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
		
		// this piece of php refreshes the page after the specified amount of time
		echo '<meta http-equiv= "refresh" content="'.$time.'; url=feedback.php">';
		
		#### Pre-cache next trial
		echo '<div class="Hidden">';
			echo show($_SESSION['Trials'][$_SESSION['Position']+1]['Stimuli']['Cue']);
			echo show($_SESSION['Trials'][$_SESSION['Position']+1]['Stimuli']['Target']);
			echo show($_SESSION['Trials'][$_SESSION['Position']+1]['Stimuli']['Answer']);
		echo '</div>';
	?>
	
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
	<script type="text/javascript">
		var timer = $("#time").html();
		var interval = 1000;
		$(".Countdown").html(timer);
		
		setInterval(countdown,interval);
		
		function countdown() {
			timer = timer-1;
			$(".Countdown").html(timer);
			if(timer < 6) {
				if(timer == 5) {
					// hide these things when timer hits 5 seconds
					$("EMBED").addClass("Hidden");
					$("#Clock").addClass("Hidden");
					$("#StepoutInstructions").addClass("Hidden");
					$("h1").addClass("Hidden");
					$("#Tetris").html("<div class=\"cont\">Get ready to continue in ...  <div class=\"Countdown\">5</div></div>");
				}
			}
		}
	</script>

</body>
</html>