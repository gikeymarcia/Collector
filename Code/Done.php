<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
    
    // redirect people who aren't supposed to be here
    if (empty($_SESSION['state'])) {
        $bounceTo = "../";
    } elseif ($_SESSION['state'] == "exp") {
        $bounceTo = $_PATH->get("Experiment Page");
    }
    if (isset($bounceTo)) {
        header("Location: $bounceTo");
        exit;
    }

    // Set the page message
    if (empty($_SESSION['next'])) {
        $email = $_SETTINGS->experimenter_email;
        $currentExperiment = $_PATH->getDefault('Current Experiment');
        $verification_code = "$_SETTINGS->verification-{$_SESSION['ID']}";
        $title   = "Done!";
        $message = "<h2>Thank you for your participation!</h2>"
                 .  "<p>If you have any questions about the experiment please email "
                 .      "<a href='mailto:$email?Subject=Comments%20on%20$currentExperiment' target='_top'>$email</a>"
                 .  "</p>";
        if ($_SETTINGS->verification != '') {
            $message .= "<h3>Your verification code is: $verification_code.</h3>";
        }
    } else {
        $next      = $_SESSION['next'];

        $username  = urlencode($_SESSION['Username']);
        $nextLink  = "http://$_SETTINGS->next_experiment";
        $nextLink .= "/login.php?Username=$username&Condition=Auto";
        $msg       = "";
        ?>
        <script type="text/javascript">
            window.location.replace("<?= $next ?>");
        </script>
        <?php
    }


    ######## Save the $_SESSION array as a JSON string
    if (($_SESSION['state'] == 'break' OR $_SESSION['state'] == 'done')
        AND !empty($_SESSION['finalJSON'])
    ) {

        $status = unserialize($_SESSION['Status']);
        $status->writeEnd($_SESSION['Start Time']);

        // preparing $_SESSION for the next run
        if ($_SESSION['state'] == 'break') {
            $_SESSION['state'] = 'return';
            $_EXPT->position++;                        // increment counter so next session will begin after the NewSession (if multisession)
            $_SESSION['Session']++;                         // increment session # so next login will be correctly labeled as the next session
            $_SESSION['ID'] = rand_string();                // generate a new ID (for next login)
        } else {
            $_SESSION['finalJSON'] = true;                  // only write this json file the first time your $_SESSION['state']='done'
        }
        
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
