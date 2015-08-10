<?php

#### checks the /Tools/ directory and returns all valid tools found
function getTools ($dir = '.') {
    $potentialTools = array();
    $contents = scandir($dir);
    foreach ($contents as $item) {
        if (($item === '.')
            OR ($item === '..')
        ) {
            continue;
        }
        if (is_dir($item)) {
            if ($item != 'Sample') {           // don't show the 'Sample' tool
                $potentialTools[] = $item;
            }
        }
    }
    $tools = array();
    foreach ($potentialTools as $check) {
        if (FileExists($check . '/' . $check . '.php', false, false)) {
            // use name as the key and file location as value
            $tools[$check] = $check . '/' . $check . '.php';
        }
    }
    return $tools;
}

function showDropdown ($options) {
    //
}

function confirmLogin () {
    if (!isset($_SESSION)) {
        session_start();
    }
    global $_SESSION;
    
    if ($_SESSION['admin']['status'] === 'loggedIn') {
        continue;
    } else {
        header('Location: ./');
        exit;
    }
}
