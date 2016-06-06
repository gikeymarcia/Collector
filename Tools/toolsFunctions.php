<?php
/**
 * Functions used to find and display Collector tools.
 */

/**
 * Gets all valid tools from the /Tools/ directory.
 *
 * @param string $dir The directory to scan (default: current directory).
 *
 * @return array Associative array of of the valid tools => paths.
 */
function getTools($dir = '.')
{
    $potentialTools = array();
    $contents = scandir($dir);
    foreach ($contents as $item) {
        // don't get the 'Sample' tool or folder markers, but get all other dirs
        if (!(($item === '.') || ($item === '..'))
            && is_dir($item)
            && $item !== 'Sample'
        ) {
            $potentialTools[] = $item;
        }
    }

    // filter out invalid tools
    $tools = array();
    foreach ($potentialTools as $check) {
        if (Collector\Helpers::fileExists($check.'/'.$check.'.php', false, 0)) {
            // use name as the key and file location as value
            $tools[$check] = $check.'/'.$check.'.php';
        }
    }

    return $tools;
}
