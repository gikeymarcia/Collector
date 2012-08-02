<!-- Collector 1.00.00 alpha2
	A program for running experiments on the web
	Copyright 2012 Mikey Garcia & Nate Kornell
-->

<?php
	require("CustomFunctions.php");							// Load custom PHP functions
	initiateCollector();

	$currentPos		=& $_SESSION['Position'];
	$currentTrial	=& $_SESSION['Trials'][$currentPos];
	// pulls in the specified timing from the order file, column "Timing"
	$time			=  trim($currentTrial['Info']['Timing']);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<link href='http://fonts.googleapis.com/css?family=Orbitron:700' rel='stylesheet' type='text/css'>
	<title>Stepout</title>
</head>
<?php flush(); ?>
<body>
<!-- 	## SET ##  Delete the next three lines to remove the constant countdown on the page-->
	<div id="Clock"><span>Seconds remaining</span>
		<div class="Countdown"></div>
	</div>
	
	
	<h1 class="stepout">Stepout</h1>
	
	<div id="StepoutInstructions">
			<p>You can tell and/or show participants just about anything using a stepout trial.  What is nice about this new soTemplate.php is that you can set the trial timing from the Orderfile<b>(no program tinkering required)</b></p>
			<p>When time is nearly up this text will disappear and a 5 second countdown will be presented on screen.</p>
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
	<script src="javascript/jquery-1.7.2.min.js" type="text/javascript"> </script>
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
				$("#Clock").addClass("Hidden");
				// $("#StepoutInstructions").addClass("Hidden");
				$("h1").addClass("Hidden");
				$("#StepoutInstructions").html("<div class=\"cont\">Get ready to continue in ...  <div class=\"Countdown\">5</div></div>");
			}
			if(timer <= 0) {
				$('#loadingForm').submit();
			}
		}
	</script>

</body>
</html>