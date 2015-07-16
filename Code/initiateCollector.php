<?php
    // start the session and error reporting
    session_start();
    error_reporting(-1);
    
    // search for root by trying to find index.php in current folder and backtracking
    $_rootF = '';
    $i = 0;
    while (!is_dir($_rootF . 'Code') AND $i<99) {
        $_rootF .= '../';
        ++$i;
    }
    $_root = realpath($_rootF);
    
    // load file locations
    require $_root.'/Code/Parse.php';
    require $_root.'/Code/fileLocations.php';
    $fileConfig = Parse::fromConfig($_root.'/Code/FileLocations.ini');
    $_FILES = new FileLocations($_root, $fileConfig);
    
    // load configs
    $config = Parse::fromConfig($_FILES->expt.'/BasicConfig.ini', true);
    
    // load custom functions
    require $_FILES->code.'/customFunctions.php';
    
    // update data path so that data will appear in Data/Collector-Data/
    $_FILES->updateParentPath('data', $_FILES->data->path. '/' . $config->experiment_name.'-Data/');
            
    // update data paths with the file extensions
    $_FILES->demographics->path .= $config->output_file_ext;
    $_FILES->status_begin->path .= $config->output_file_ext;
    $_FILES->status_end->path .= $config->output_file_ext;
    $_FILES->final_questions_data->path .= $config->output_file_ext;
    $_FILES->instructions_data->path .= $config->output_file_ext;
