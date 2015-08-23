<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    if (!isset($_SESSION)) {
        $_root = '..';
        
        // load file locations
        require $_root.'/Code/Pathfinder.class.php';
        $_PATH = new Pathfinder();
        
        require $_PATH->get('Parse');
        
        // load configs
        $_CONFIG = Parse::fromConfig($_PATH->get('Config'), true);

        // load custom functions
        require $_PATH->get('Custom Functions');
    }
    
    #### variables needed for this page
    $folder  = $_PATH->ineligibility_dir;           // where to look for files containing workers
    $files   = scandir($folder);                    // list all files containing workers
    $toCheck = null;                                // who to check for eligibility
    $checked = array();                             // list of all the files that were checked
    $skipped = array();                             // list of all files skipped becasue there was no 'WorkerID' column
    $current = array();                             // each file will be loaded into this array
    $uniques = array();                             // all the unique workers (no duplicates)
    $noGo    = array();                             // reasons to exclude someone from participation
    $ip = $_SERVER["REMOTE_ADDR"];                  // user's ip address
    $ipFilename = 'rejected-IPs.csv';               // name of bad IP file
    $ipPath = $folder . $ipFilename;                // path to bad IP file
    $priorExp = false;                              // temporary hack to block people by username who are on whitelisted IPs
    
    #### functions needed to make this page work
    
    // Prints errors and stops users from logging in if errors are found
    function rejectCheck ($errors) {
        if (count($errors) > 0) {
            foreach ($errors as $stopper) {
                echo '<h2>' . $stopper . '</h2>';
            }
            if (isset($_SESSION)) {
                exit;
            }
        }
    }
    
    // Adds user's IP to log file, if the file doesn't exist it will be created
    function logIP() {
        global $ipPath, $ip;

        if (!is_file($ipPath)) {
            $ipFile = fopen($ipPath, 'a');
            fputs($ipFile, 'ip address');           // write header
            fputs($ipFile, PHP_EOL);                // write newline character
            fputs($ipFile, $ip);                    // write IP to file
            fputs($ipFile, PHP_EOL);                // write newline character
        } else {
            $ipFile = fopen($ipPath, 'a');
            fputs($ipFile, $ip);                    // write IP to file
            fputs($ipFile, PHP_EOL);                // write newline character
        }
    }
    
    
    #### make a master list of unique user IDs (lowercase and trimmed)
    foreach ($files as $file) {                                     // check all files
        
        // set correct delimiter and skip IP log file
        if (inString('.txt', $file)) {
            $delimiter = "\t";
        } elseif (inString('.csv', $file)) {
            $delimiter = ',';
        } else { continue; }
        if ($file == $ipFilename) {                                 // skip reading IP file
            continue;
        }
        
        $current = array();                                         // clear data from current file before loading next one
        $current = GetFromFile($folder . $file, false, $delimiter); // read a file presumably containing workers
        if (!isset($current[0]['WorkerId'])) {                      // skip files without a 'WorkerID' column
            $skipped[] = $file;                                     // keep track of which files we've skipped
        } else {
            $checked[] = $folder . $file;                           // keep track of which files we've checked
            foreach ($current as $worker) {
                if (!in_array($worker['WorkerId'], $uniques)) {
                    $uniques[] = trim(strtolower($worker['WorkerId']));
                }
            }
        }
    }


    #### show prompt if checking while not logged in
    if (isset($_SESSION)) {                                         // if there is a session initiated already
        $toCheck = $_SESSION['Username'];                           // use username if logged in
    } else {
        echo '<form method="POST" action="">
                  <p> Whose eligibility would you like to check?
                     <br/>
                     <em>Checking from ' . count($uniques) . ' workers within ' . count($checked) . ' files</em>
                  </p>
                  <input type="text" name="worker" class="eCheck" />
                  <input type="submit" value="Eligible?" />
              </form>';
        if (isset($_POST['worker'])) {
            $toCheck = $_POST['worker'];
        }
    }

    #### running checks
    if (isset($toCheck)) {                                          // if there is something to check then check it
        $noCaseCheck = trim(strtolower($toCheck));                  // all lowercase version of ID to check

        ####  check if we've already told this person not to come back (BOOM, headshot)
        // if (isset($_SESSION)
            // AND file_exists($ipPath)
        // ) {
            // $badIPs = GetFromFile($ipPath, false);
            // foreach ($badIPs as $rejected) {
                // if ($ip == $rejected['ip address']) {
                    // $noGo[] = 'Sorry, you are not allowed to login to this experiment more than once.';
                    // break;     // only log IP rejection once
                // }
            // }
        // }

        #### if blacklist is enabled, add IP to reject list
        // if (isset($_SESSION)
            // AND ($config->blacklist == true)
        // ) {
            // logIP();
        // }

        #### check if this user has previously participated
        if (in_array($noCaseCheck, $uniques)) {
            $noGo[] = 'Sorry, you are not eligible to participate in this study
                       because you have participated in a previous version of
                       this experiment.';
            $priorExp = true;
            // if ($config->blacklist == true) {
                // logIP();
            // }
        }

        if (!(in_array($ip, $_CONFIG->whitelist, false))) {          // skip the autocheck if IP is allowed
            rejectCheck($noGo);                             // print errors
        } elseif ($priorExp == true) {
            echo '<h2>Sorry, you are not eligible to participate in this study
                      because you have participated in a previous version of
                      this experiment.</h2>';
            if (isset($_SESSION)) {
                exit;
            }
        }
    }

    #### check if this user has previously logged in
        /*
         * This will be completed once I finish updating some other functionality
         *
         * Planned functionality once completed:
         *      if a user tries to login and has not been rejected for IP or previous involvment
         *   then this function will load up user session, figure out where they last were,
         *   reload all stimuli, and continue experiment.
         *
         *   users who are restarted in this way should be denoted as special cases in status.txt
         */


    if ((count($noGo) == 0)
        AND (isset($toCheck))
        AND (!isset($_SESSION))
    ) {
        echo '<h2>User <b>' . $toCheck . '</b> is eligible to participate</h2>';
    }
    // show all users to people who want to login
    if (!isset($_SESSION)) {
        Readable($files, 'Files in directory');
        Readable($uniques, 'Previous iteration workers');
        Readable($skipped, 'Files skipped becasue there is no "WorkerID" column');
    }

    if (!isset($_SESSION)) {
        echo '<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"> </script>';
        echo '<script src="javascript/collector_1.0.0.js" type="text/javascript"> </script>';
    }

    #### style to make the page look right
    echo '<style>
            .eCheck { background:#A4DBFC; }
            p { font-size: 1.3em; }
          </style>';
    ####################
