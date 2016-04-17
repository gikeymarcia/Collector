<?php
if (!isset($_SESSION)) {
    $_root = '..';

    // load file locations
    require $_root.'/Code/Pathfinder.class.php';
    $_PATH = new Collector\Pathfinder();

    require $_PATH->get('Parse');

    // load configs
    $_SETTINGS = adamblake\Parse::fromConfig($_PATH->get('Config'), true);

    // load custom functions
    require $_PATH->get('Custom Functions');
}

/*
 * Location of files containing workers.
 * @var string
 */
$folder = $_PATH->ineligibility_dir;

/*
 * List all files containing workers.
 * @var array
 */
$files = scandir($folder);

/*
 * Who to check for eligibility.
 */
$toCheck = null;

/*
 * List of all the files that were checked.
 * @var array
 */
$checked = array();

/*
 * List of all files with no 'WorkerID' column.
 * @var array
 */
$skipped = array();

/*
 * The loaded files.
 * @var array
 */
$current = array();

/*
 * All the unique worker IDs.
 * @var array
 */
$uniques = array();

/*
 * Reasons to exclude someone from participation.
 * @var array
 */
$noGo = array();

/*
 * User's IP address.
 * @var string
 */
$ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_SANITIZE_STRING);

/*
 * Filename of the list of rejected IPs.
 * @var string
 */
$ipFilename = 'rejected-IPs.csv';

/*
 * Full path to the list of rejected IPs.
 * @var string
 */
$ipPath = realpath($folder.$ipFilename);

/*
 * Temporary hack to block people by username who are on whitelisted IPs.
 */
$priorExp = false;

/**
 * Prints errors and stops users from logging in if errors are found.
 *
 * @param array $errors Errors to print.
 */
function rejectCheck($errors)
{
    if (count($errors) > 0) {
        foreach ($errors as $stopper) {
            echo '<h2>'.$stopper.'</h2>';
        }

        if (isset($_SESSION)) {
            exit;
        }
    }
}

/**
 * Adds user's IP to the log file, if the file doesn't exist it will be created.
 *
 * @global string $ipPath Full path to the list of rejected IPs.
 * @global string $ip     User's IP address.
 */
function logIP()
{
    global $ipPath, $ip;

    // clear the cache for the log file and get the filesize
    clearstatcache(true, $ipPath);
    $filesize = filesize($ipPath);

    $handle = fopen($ipPath, 'a');
    if ($filesize === 0) {
        // file write header
        fputs($handle, 'ip address');
        fputs($handle, PHP_EOL);
    }

    // add IP to file
    fputs($handle, $ip);
    fputs($handle, PHP_EOL);

    fclose($handle);
}

/*
 * Make a master list of unique user IDs (lowercase and trimmed)
 */
foreach ($files as $file) {
    // set correct delimiter and skip IP log file
    if (inString('.txt', $file)) {
        $delimiter = "\t";
    } elseif (inString('.csv', $file)) {
        $delimiter = ',';
    } else {
        continue;
    }

    // do not read IP log file
    if ($file === $ipFilename) {
        continue;
    }

    // read a file presumably containing workers
    $current = getFromFile($folder.$file, false, $delimiter);

    // skip files without a 'WorkerID' column and keep track skipped files
    if (!isset($current[0]['WorkerId'])) {
        $skipped[] = $file;
    } else {
        // check file and keep track of checked files
        $checked[] = $folder.$file;
        foreach ($current as $worker) {
            if (!in_array($worker['WorkerId'], $uniques)) {
                $uniques[] = trim(strtolower($worker['WorkerId']));
            }
        }
    }
}

/*
 * Show prompt if checking while not logged in
 */

// use username if logged in
if (isset($_SESSION)) {
    $toCheck = $_SESSION['Username'];
} else {
    echo '<form method="POST" action="">
              <p> Whose eligibility would you like to check?
                 <br/>
                 <em>Checking from '.count($uniques).' workers within '.count($checked).' files</em>
              </p>
              <input type="text" name="worker" class="eCheck" />
              <input type="submit" value="Eligible?" />
          </form>';
    if (isset($_POST['worker'])) {
        $toCheck = $_POST['worker'];
    }
}

/*
 * Run the checks
 */
if (isset($toCheck)) {
    $noCaseCheck = trim(strtolower($toCheck));

//    // check if this person has already been rejected
//     if (isset($_SESSION) && file_exists($ipPath)) {
//         $badIPs = getFromFile($ipPath, false);
//         foreach ($badIPs as $rejected) {
//             // log and break at the first IP rejection
//             if ($ip === $rejected['ip address']) {
//                 $noGo[] = 'Sorry, you are not allowed to login to this experiment more than once.';
//                 break;
//             }
//         }
//     }
//
//    // when blacklist is enabled, add IP to reject list
//     if (isset($_SESSION) && ($config->blacklist == true)) {
//         logIP();
//     }

    // check if this user has previously participated
    if (in_array($noCaseCheck, $uniques)) {
        $noGo[] = 'Sorry, you are not eligible to participate in this study
                   because you have participated in a previous version of
                   this experiment.';
        $priorExp = true;

//         if ($config->blacklist === true) {
//             logIP();
//         }
    }

    // skip the autocheck if IP is allowed
    if (!(in_array($ip, $_SETTINGS->whitelist, false))) {
        // print errors
        rejectCheck($noGo);
    } elseif ($priorExp === true) {
        echo '<h2>Sorry, you are not eligible to participate in this study
                  because you have participated in a previous version of
                  this experiment.</h2>';
        if (isset($_SESSION)) {
            exit;
        }
    }
}

// check if this user has previously logged in
/*
 * @todo finish implementing check.php
 * This will be completed once I finish updating some other functionality
 *
 * Planned functionality once completed:
 * - if a user tries to login and has not been rejected for IP or previous involvment
 *   then this function will load up user session, figure out where they last were,
 *   reload all stimuli, and continue experiment.
 *
 * - users who are restarted in this way should be denoted as special cases in status.txt
 */

if ((count($noGo) == 0) && (isset($toCheck)) && (!isset($_SESSION))) {
    echo "<h2>User <b>{$toCheck}</b> is eligible to participate</h2>";
}
// show all users to people who want to login
if (!isset($_SESSION)) {
    Readable($files, 'Files in directory');
    Readable($uniques, 'Previous iteration workers');
    Readable($skipped, 'Files skipped because there is no "WorkerID" column');
}

if (!isset($_SESSION)) {
    echo '<script src="http://code.jquery.com/jquery-1.10.2.min.js" type="text/javascript"> </script>';
    echo '<script src="javascript/collector_1.0.0.js" type="text/javascript"> </script>';
}

?>

<!-- page styles -->
<style>
  .eCheck {
    background:#A4DBFC;
  }
  p {
    font-size:1.3em;
  }
</style>
