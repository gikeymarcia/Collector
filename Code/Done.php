<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
    
    // Set the page message
    if ($_SETTINGS->next_experiment == false) {
        $email = $_SETTINGS->experimenter_email;
        $currentExperiment = $_PATH->getDefault('Current Experiment');
        $verification_code = "$_SETTINGS->verification-{$_SESSION['ID']}";
        $title   = "Done!";
        $message = "<h2>Thank you for your participation!</h2>"
                 .  "<p>If you have any questions about the experiment please email "
                 .      "<a href='mailto:$email?Subject=Comments%20on%20$currentExperiment' target='_top'>$email</a>"
                 .  "</p>";
        if ($_SETTINGS->mTurk_mode == true) {
            $message .= "<h3>Your verification code is: $verification_code.</h3>";
        }
    } else {
        $title     = "Quick Break";
        $message   = "<h2>Experiment will resume in 5 seconds.</h2>";

        $username  = urlencode($_SESSION['Username']);
        $nextLink  = "http://$_SETTINGS->next_experiment";
        $nextLink .= "/login.php?Username=$username&Condition=Auto";
        ?>
        <script type="text/javascript">
            window.location.replace("<?= $nextLink ?>");
        </script>
        <?php
    }


    ######## Save the $_SESSION array as a JSON string
    // do these things if we aren't at the end of the experiment
    if ($_SESSION['state'] == 'break') {

        $status = unserialize($_SESSION['Status']);
        $status->writeEnd($_SESSION['Start Time']);

        // preparing $_SESSION for the next run
        $_SESSION['state'] = 'return';
        $_SESSION['Position']++;                        // increment counter so next session will begin after the NewSession (if multisession)
        $_SESSION['Session']++;                         // increment session # so next login will be correctly labeled as the next session
        $_SESSION['ID'] = rand_string();                // generate a new ID (for next login)
        
        $jsonSession = json_encode($_SESSION);              // encode the entire $_SESSION array as a json string
        $jsonPath = $_PATH->get('json');
        
        if (!is_dir($_PATH->get('JSON Dir'))) {
            // make the folder if it doesn't exist
            mkdir($_PATH->get('JSON Dir'), 0777, true);
        }
        file_put_contents($jsonPath, $jsonSession);
        #######
    }
    
        
    require $_PATH->get('Header');
?>
    <form id="content">
        <?php echo $message; ?>
    </form>
    
    <style>
        #content {
            width: 500px;
            text-rendering: optimizeLegibility;
        }
    </style>
<?php
    require $_PATH->get('Footer');
?>
