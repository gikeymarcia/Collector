<?php

require '../../initiateTool.php';
require 'fileReadingFunctions.php';

function read_directory($dir) {
    if (!is_dir($dir)) return false;
    
    $dir_scan = scandir($dir);
    
    $dir_data = array();
    
    foreach ($dir_scan as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        
        $path = "$dir/$entry";
        
        if (is_file($path)) {
            if (strtolower(substr($entry, -4)) === '.csv') {
                $contents = read_csv_raw($path);
            } else {
                $contents = file_get_contents($path);
            }
            
            $dir_data[$entry] = $contents;
        } elseif (is_dir($path)) {
            $dir_data[$entry] = read_directory($path);
        }
    }
    
    return $dir_data;
}

if (!is_dir('New Experiment')) {
    exit('To create a new default for a new experiment, a folder called "New Experiemnt" must exist inside the ExperimentEditor/ directory, inside Admin/Tools');
}

$dir_data = read_directory('New Experiment');

file_put_contents(
    'default_new_experiment.json',
    json_encode($dir_data)
);

echo 'Done';
