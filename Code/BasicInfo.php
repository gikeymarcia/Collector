<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	ini_set('auto_detect_line_endings', true);				// fixes problems reading files saved on mac
	session_start();										// start the session at the top of each page
	if ($_SESSION['Debug'] == FALSE) {
		error_reporting(0);
	}
	require 'CustomFunctions.php';							// Loads all of my custom PHP functions
	require 'fileLocations.php';							// sends file to the right place
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<title>Basic Information</title>
</head>
<?php flush(); ?>
<body>
	<div class=main-contain>
		<h2 class=textcenter>Basic Information</h2>

	<form name=Demographics class=collector-form action="BasicInfoData.php" method=post autocomplete=off>

		<div class=field>
			<legend>What is your gender?</legend>
			<input type="radio"	value="Male"	class="radio"	name="Gender"	/>	Male	<br />
			<input type="radio"	value="Female"	class="radio"	name="Gender"	/>	Female	<br />
		</div>

		<div class=field>
			<p>What is your age?</p>
			<input type="text" value="" name="Age" autocomplete="off" />
		</div>

		<div class=field>
			<p>Which of the following best describes your highest achieved education level?</p>
			<select name="Education">
				<option selected="selected">Select Level</option>
				<option>	Some High School							</option>
				<option>	High School Graduate						</option>
				<option>	Some college, no degree						</option>
				<option>	Associates degree							</option>
				<option>	Bachelors degree							</option>
				<option>	Graduate degree (Masters, Doctorate, etc.)	</option>
			</select>
		</div>

		<div class=field>
			<p>Do you speak English fluently?</p>
			<input	type="radio"	name="English"	value="Fluent"		/>	Yes, I am fluent in English		<br />
			<input	type="radio"	name="English"	value="Non-Fluent"	/>	No, I am not fluent in English	<br />
		</div>

		<div class=field>
			<p>In what country do you live?</p>
			<input type="text" value="" name="Country" size="30"	autocomplete="off" />
		</div>

                <!-- ## SET ##  Use this area to provide the equivalent of an informed consent form -->
		<div class=consent>
			<h3 class=consent-legend>Informed Consent:</h3>
			<h3 class="consent-legend textcenter">Learning Words and Remembering Facts</h3>
					<textarea rows=20 cols=45 wrap=physical>This is the informed consent form.  You can put whatever you want here.</textarea>
		</div>

		<div class="consent textcenter">
			<input class=button type=submit value="Submit Basic Info" />
		</div>
		</form>
	</div>
</body>
</html>
