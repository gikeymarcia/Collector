<?php
/*	Collector
	A program for running experiments on the web
	Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
	ini_set('auto_detect_line_endings', true);			// fixes problems reading files saved on mac
	require 'CustomFunctions.php';						// Load custom PHP functions
	require 'fileLocations.php';						// sends file to the right place
	initiateCollector();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="css/global.css" rel="stylesheet" type="text/css" />
	<link href='http://fonts.googleapis.com/css?family=Kreon' rel='stylesheet' type='text/css' />
	<title>Final Questions</title>
</head>
<?php flush(); ?>
<body>
	
	<h1>Final Questions</h1>
	
	<?php
		// if this is the first time on FinalQuestions.php then load questions from file
		if(isset($_SESSION['FinalQs']) == FALSE) {
			$fQ	= GetFromFile($up.$expFiles.'FinalQuestions.txt');
			// loop that deletes trailing empty positions from $fQ array
			for ($i=count($fQ)-1; $i >0; $i--) { 
				if($fQ[$i] == null) {
					unset($fQ[$i]);
				}
				else {
					break;
				}
			}
			$_SESSION['FinalQs']	= $fQ;
			$_SESSION['FQpos']		= 2;
		}
		
		
		// sends to done.php if there are no more questions
		if(isset($_SESSION['FinalQs'][ $_SESSION['FQpos'] ]) == FALSE) {
			echo '<meta http-equiv="refresh" content="0; url=done.php">';
		}
		
		
		// setting up aliases (makes all later code easier to read)
		$allFQs		=&	$_SESSION['FinalQs'];
		$pos		=&	$_SESSION['FQpos'];
		$FQ			=&	$allFQs[$pos];							// all info about current final question
		$Q			=	$FQ['Question'];						// the question on this trial
		$type		=	trim(strtolower($FQ['Type']));			// type of question to display for this trial (i.e, likert, text, radio, checkbox)
		$options	=	array(0=> NULL);
		
		
		// loading values into $options
		for ($i=1; isset($FQ[$i]); $i++) {
			if($FQ[$i] != '') {
				$rawString	= $FQ[$i];
				$split		= explode('|', $rawString);
				$temp		= array( 'value' => $split[0], 'text' => $split[1]);
				$options[]	= $temp;
				// echo 'found fq #'.$i.'  and it is  '.$FQ[$i].'<br />';
			}
		}
		
		
		// readable($allFQs, 'all FinalQuestions');						#### DEBUG ####
		// readable($options, 'options');								#### DEBUG ####
		// echo "current question type is: {$type} <br /> <br />";		#### DEBUG ####
		
		
		// if the question starts with '*' then skip it; good for skipping questions when debugging without deleting finalQuestions
		if($Q[0] == '*') {
			echo '<meta http-equiv="refresh" content="0; url=FQdata.php">';
			exit;
		}
		?>
		
		
		<div id="FQlocation">
			<div id="FQ"><?php echo $Q ?></div>
			<form name="FinalQuestion"  autocomplete="off"  action="FQdata.php"  method="post">
						<?php
							// radio button code
							if($type == 'radio') {
								echo "<ul>";
								foreach ($options as $choice) {
									if($choice != '') { 
										echo '<li>
													<input type="radio" name="formData" value="'.$choice["value"].'"/>'.$choice["text"].'
											  </li>';
									}
								}
								echo '<li>
											<input type="submit" value="Submit" id="FormSubmitButton" />
									</li>
								</ul>';
							}
							// checkbox code
							elseif($type == 'checkbox') {
								echo '<ul>';
								foreach ($options as $choice) {
									if($choice != '') { 
										echo '<li>
													<input type="checkbox" name="formData[]" value="'.$choice["value"].'"/>'.$choice["text"].'
											  </li>';
									}
								}
								echo '<li>
											<input type="submit" value="Submit" id="FormSubmitButton" />
									  </li>
								</ul>';
							}
							// likert code
							elseif($type == 'likert') {
								?>
								<ul>
									<div id="Likert">
										<li>
											<input type="radio" name="formData" value="1"/><p>1</p>
										</li>
										
										<li>
											<input type="radio" name="formData" value="2"/><p>2</p>
										</li>
										
										<li>
											<input type="radio" name="formData" value="3"/><p>3</p>
										</li>
										
										<li>
											<input type="radio" name="formData" value="4"/><p>4</p>
										</li>
										
										<li>
											<input type="radio" name="formData" value="5"/><p>5</p>
										</li>
										
										<li>
											<input type="radio" name="formData" value="6"/><p>6</p>
										</li>
										
										<li>
											<input type="radio" name="formData" value="7"/><p>7</p>
										</li>
									</div>
								</ul>
								<br style="clear: both;" />
								<input type="submit" value="Submit" id="FormSubmitButton"/>
								<?php
							}
							// code to create textbox final question
							elseif ($type == 'text') {?>
								<ul>
									<li>
										<input type="text" name="formData" class="Textbox" autocomplete="off" />
									</li>
									
									<li>
										<input type="submit" value="Submit" id="FormSubmitButton"/>
									</li>
								</ul>
								<?php
							}
						?>
				<input name="RT" class="RT Hidden" type="text" value=""/> <!-- Hidden field to capture reaction time -->
			</form>
		</div>
	
	<!-- textbox final question -->
<!-- 	<div id="FQlocation">
		<div id="FQ">This is the place where you can ask textbox questions.</div>
		<form name="FinalQuestion" action="FQdata.php" method="post">
			<ul>
				<li>
					<input type="text" class="Textbox" />
				</li>
				
				<li>
					<input type="submit" value="Submit" id="FormSubmitButton"/>
				</li>
			</ul>
			<input name="RT" class="RT Hidden" type="text" value=""/>
		</form>
	</div> -->
	
	<!-- Likert scale question -->
<!-- 	<div id="FQlocation">
		<div id="FQ">
			Ask whatever likert question you would like to
			<p>If you follow the question with paragraph tag you can describe your 1-7 scale</p>
		</div>
		<form name="FinalQuestion" action="FQdata.php" method="post">
			<ul>
				<div id="Likert">
					<li>
						<input type="radio" name="RadioButton" value="1"/><p>1</p>
					</li>
					
					<li>
						<input type="radio" name="RadioButton" value="2"/><p>2</p>
					</li>
					
					<li>
						<input type="radio" name="RadioButton" value="3"/><p>3</p>
					</li>
					
					<li>
						<input type="radio" name="RadioButton" value="4"/><p>4</p>
					</li>
					
					<li>
						<input type="radio" name="RadioButton" value="5"/><p>5</p>
					</li>
					
					<li>
						<input type="radio" name="RadioButton" value="6"/><p>6</p>
					</li>
					
					<li>
						<input type="radio" name="RadioButton" value="7"/><p>7</p>
					</li>
				</div>
			</ul>
			<br />
			<input type="submit" value="Submit" id="FormSubmitButton"/>
			<input name="RT" class="RT Hidden" type="text" value=""/>
		</form>
	</div> -->
	
	<!-- checkbox questions -->
<!-- 	<div id="FQlocation">
		<div id="FQ">This is the place where the you can ask questions that will allow multiple answers.</div>
		<form name="FinalQuestion" action="FQdata.php" method="post">
			<ul>
				<li>
					<input type="checkbox" name="options[]" value="one"/>Say this crap
				</li>
				
				<li>
					<input type="checkbox" name="options[]" value="two"/>some other crapola
				</li>
				
				<li>
					<input type="checkbox" name="options[]" value="three"/>More crap to ask
				</li>
				
				<li>
					<input type="submit" value="submit" id="FormSubmitButton"/>
				</li>
			</ul>
			<input name="RT" class="RT Hidden" type="text" value=""/>
		</form>
	</div> -->
	
	<!-- radio button code -->
<!-- 	<div id="FQlocation">
		<div id="FQ">This is the place where you can ask questions which will use radio buttons.</div>
		<form name="FinalQuestion" action="FQdata.php" method="post">
			<ul>
				<li>
					<input type="radio" name="RadioButton" value="one"/>Say this crap
				</li>
				
				<li>
					<input type="radio" name="RadioButton" value="two"/>some other crapola
				</li>
				
				<li>
					<input type="radio" name="RadioButton" value="three"/>More crap to ask
				</li>
				
				<li>
					<input type="submit" value="Submit" id="FormSubmitButton"/>
				</li>
			</ul>
			<input name="RT" class="RT Hidden" type="text" value=""/>
		</form>
	</div> -->
	
	<script src="http://code.jquery.com/jquery-1.8.0.min.js" type="text/javascript"> </script>
	<!-- This script was meant for instructions but does what I need for here (updates RT)-->
	<script src="javascript/jsCode.js" type="text/javascript"> </script>

<!--################# to do #################

-->

</body>
</html>