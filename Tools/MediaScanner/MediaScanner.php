<?php

adminOnly();
/**
 * Scan a directory and all of it's subdirectories up to a specified depth
 * Returns an array of only the paths that point to files
 * @param  string  $dir       Path to the directory we are scanning
 * @param  integer $max_depth maximum levels of subdirectories to scan
 * @return array             list of string paths that point to files within the $dir
 */
function scandir_recursive($dir, $max_depth = 25) {
    $slash = "/";
    if ($max_depth < 1) {
        return false;
    }
    $output = array();
    $initial = scandir($dir);
    if ($initial === false) { return false; }
    foreach ($initial as $pos => $path) {
        if ($path == "." OR $path == "..") { continue; }
        $relative = $dir . $slash . $path;
        if (is_dir($relative)) {
            $next = scandir_recursive($relative, ($max_depth-1));
            if ($next !== null) {
                foreach ($next as $suffix) {
                    $output[] = $suffix;
                }
            }
        } elseif (is_file($relative)) {
            $output[] = $relative;  
        }
    }
    return $output;
}

$types = array("jpg","jpeg","png","gif","wav","mp3","mp4");
$media_dir = $_PATH->get("Media");
$max_depth = 50;

$results = scandir_recursive($media_dir, $max_depth);

$types = array_flip($types);        // makes it easier to check using isset($types['mp3'])
$endOfPrefix = "Common/Media/";     // Used to trim paths to be relative to the Media folder
$endLen = strlen($endOfPrefix);     // Length of trimming selector

/**
 * Go through all files and remove any files whose extensions are not in the
 * $types array.
 * All remaining file paths are trimmed to paths relative to "Experiments/Common/Media"
 */
foreach ($results as $pos => &$path) {
    $bits = pathinfo($path);
    if (!isset(  $types[ strtolower($bits['extension']) ]  )) {
        unset($results[$pos]);
    } else {
        $end = stripos($path, $endOfPrefix) + $endLen;
        $path = substr($path, $end);
    }
}
$results = array_values($results);

// Write a temporary .csv file with Media contents
$temp = fopen("MediaScanner/tempStim.csv", "w+");
fputcsv($temp, array('Cue', 'Answer'));
foreach ($results as $readyPath) {
    $info = pathinfo($readyPath);
    fputcsv($temp, array($readyPath, $info['filename']));
}
fclose($temp);
$show = getFromFile("MediaScanner/tempStim.csv");






?>

<div class="toolWidth">
    <h2>Here is what we found in your Media folder</h2>
    <?php display2dArray($show); ?>
    <div id="dl">
        <a href="MediaScanner/download.php" target="_blank"
        >Click here to downoad the file.</a>
    </div>
</div>

<style type="text/css" media="screen">
    .display2dArray td       { max-width:30em; }
    .display2dArray {
        font-size: 16pt;
        background-color: #dee7ec;
        padding-bottom: 2em;    
    }
    td:first-child {
        font-weight:700;
    }
    .toolWidth {
        overflow: visible;
        text-align: left;
    }

    #dl {   
        margin-top: 1em;
        font-size: 32pt;
        text-align: left;
    }
</style>