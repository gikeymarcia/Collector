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
	   <link href="css/jquery-ui-1.10.4.custom.min.css" rel="stylesheet" type="text/css" />
	<title>Final Questions</title>
</head>
<?php flush(); ?>
<body>

<?php
    // if this is the first time on FinalQuestions.php then load questions from file
    if(isset($_SESSION['FinalQs']) == FALSE) {
        $fQ = GetFromFile($up.$expFiles.'FinalQuestions.txt');
        // loop that deletes trailing empty positions from $fQ array
        for ($i=count($fQ)-1; $i >0; $i--) {
            if($fQ[$i] == null) {
                unset($fQ[$i]);
            }
            else {
                break;
            }
        }
        $_SESSION['FinalQs']    = $fQ;
        $_SESSION['FQpos']      = 2;
    }


    // sends to done.php if there are no more questions
    if(isset($_SESSION['FinalQs'][ $_SESSION['FQpos'] ]) == FALSE) {
        echo '<meta http-equiv="refresh" content="0; url=done.php">';
    }


    // setting up aliases (makes all later code easier to read)
    $allFQs     =&  $_SESSION['FinalQs'];
    $pos        =&  $_SESSION['FQpos'];
    $FQ         =&  $allFQs[$pos];                          // all info about current final question
    $Q          =   $FQ['Question'];                        // the question on this trial
    $type       =   trim(strtolower($FQ['Type']));          // type of question to display for this trial (i.e, likert, text, radio, checkbox)
    $options    =   array();


    // loading values into $options
    for ($i=1; isset($FQ[$i]); $i++) {
        if($FQ[$i] != '') {
            $rawString  = $FQ[$i];
            $split      = explode('|', $rawString);
            $temp       = array( 'value' => $split[0], 'text' => $split[1]);
            $options[]  = $temp;
            // echo 'found fq #'.$i.'  and it is  '.$FQ[$i].'<br />';
        }
    }


    // readable($allFQs, 'all FinalQuestions');                     #### DEBUG ####
    // readable($options, 'options');                               #### DEBUG ####
    // echo "current question type is: {$type} <br /> <br />";      #### DEBUG ####


    // if the question starts with '*' then skip it; good for skipping questions when debugging without deleting finalQuestions
    if($Q[0] == '*') {
        echo '<meta http-equiv="refresh" content="0; url=FQdata.php">';
        exit;
    }
?>

    <div class=cframe-outer>
        <div class=cframe-inner>
            <div class=cframe-content>
                <h1 class=textcenter>Final Questions</h1>
                <p><?php echo $Q ?></p>

                <form class=collector-form name=FinalQuestion autocomplete=off action="FQdata.php" method=post>

                    <?php
                    // radio button code
                    if($type == 'radio'): ?>
                        <?php foreach ($options as $choice): ?>
                        <label>
                            <input type=radio name=formData value='<?php echo $choice["value"]; ?>' />
                            <?php echo $choice["text"]; ?>
                        </label>
                        <?php endforeach; ?>
                        <div class=textcenter>
                            <input class='button' id=FormSubmitButton type=submit value="Submit" />
                        </div>
                    <?php endif;

                    // checkbox code
                    if($type == 'checkbox'): ?>
                        <?php foreach ($options as $choice): ?>
                        <label>
                            <input type=checkbox name=formData value='<?php echo $choice["value"]; ?>' />
                            <?php echo $choice["text"]; ?>
                        </label>
                        <?php endforeach; ?>
                        <div class=textcenter>
                            <input class='button' id=FormSubmitButton type=submit value="Submit" />
                        </div>
                    <?php endif;

                    // likert code
                    if($type == 'likert'): ?>
                        <div id=slider></div>
                        <div class=amount>
                            <input name=formData type=text id=amount />
                            <input class='button' id=FormSubmitButton type=submit value="Submit" />
                        </div>
                    <?php endif;

                    // textbox code
                    if ($type == 'text'): ?>
                        <div class=textcenter>
                            <input type=text name=formData autocomplete=off />
                            <input class='button' id=FormSubmitButton type=submit value="Submit" />
                        </div>
                    <?php endif; ?>

                    <input name=RT id=RT class=hidden type=text value=""/>

                </form>
            </div>
        </div>
    </div>


	<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"> </script>
	<script src="javascript/jquery-ui-1.10.4.custom.min.js" type="text/javascript"> </script>
	<!-- This script was meant for instructions but does what I need for here (updates RT)-->
	<script src="javascript/jsCode.js" type="text/javascript"> </script>

</body>
</html>