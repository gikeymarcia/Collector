<?php
	ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
	session_start();										// start the session at the top of each page
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require("CustomFunctions.php");							// Loads all of my custom PHP functions
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Basic Information</title>
</head>
<?php flush(); ?>
<body>
	
	<div id="content">
		<h2>Basic Information</h2>
		
	<form name="Demographics" action="BasicInfoData.php" method="post"	autocomplete="off">
		
		<div class="Question">
			<p class="Ask">What is your gender?</p>
			<input type="radio" value="Male" class="radio" name="Gender"> Male <br />
			<input type="radio" value="Female" class="radio" name="Gender" /> Female<br />
		</div>
		
		<div class="Question">
			<p class="Ask">What is your age?</p>
			<input type="text" value="" name="Age" autocomplete="off" />
		</div>
				
		<div class="Question">
			<p class="Ask">Which of the following best describes your highest achieved education level?</p>
			<select name="Education">
				<option selected="selected">Select Level</option>
				<option>Some High School</option>
				<option>High School Graduate</option>
				<option>Some college, no degree</option>
				<option>Associates degree</option>
				<option>Bachelors degree</option>
				<option>Graduate degree (Masters, Doctorate, etc.)</option>
			</select>
		</div>
				
		<div class="Question">
			<p class="Ask">Do you speak English fluently?</p>
			<input type="radio" name="English" value="Fluent" />Yes, I am fluent in English<br />
			<input type="radio" name="English" value="Non-Fluent" />No, I am not fluent in English<br />
		</div>
				
		<div class="Question">
			<p class="Ask">In what country do you live?</p>
			<input type="text" value="" name="Country" size="30"	autocomplete="off" />
		</div>
		
<!-- 		## SET ##	The text below should be set.  Usually we use this area for providing the equivalent of an informed consent form -->
		<div id="Constent">Informed Consent: Learning Words and Remembering Facts</div>	
		<textarea rows="20" cols="60" wrap="physical">This is the informed consent form.  You can put whatever you want here.</textarea>
		
		<br />
		
		<input type="submit" value="Submit Basic Info" />
		
	</form>
	
	</div>
</body>
</html>