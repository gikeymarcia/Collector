<?php
function getTools() {
    $toolsDir = __DIR__ . '/Tools';
    $tools    = scandir($toolsDir);

    foreach ($tools as $i => $tool) {
        if (   $tool === '.'
            || $tool === '..'
            || !is_dir("$toolsDir/$tool")
            || !is_file("$toolsDir/$tool/index.php")
        ) {
            unset($tools[$i]);
        }
    }
    
    return $tools;
}

function determineCurrentTool() {
    /* need to find which folder inside Tools/ we are in.
       We can use $_SERVER['SCRIPT_FILENAME'] to find the
       script we are trying to run, inside a tool. However,
       we need to back up to the Admin folder, without
       knowing if someone has made their own Admin folder
       inside a tool. Using __DIR__ can tell us the actual
       Admin folder that holds this file.
       By comparing these two, we can determine which tool
       we are actually in. */
    /* interesting note: on a Windows OS, __DIR__ will use "\"
       as its directory separator, while SCRIPT_FILENAME will
       still use "/". */
    /* spaces can be used in tool names, at least with xampp */
    $script   =  filter_input(INPUT_SERVER, 'SCRIPT_FILENAME');
 // $script   == "C:/localhost/Collector/Admin/Tools/GetData/index.php"
    $toolsDir =  __DIR__;
 // $toolsDir == "C:\localhost\Collector\Admin"
    $toolsDir =  str_replace('\\', '/', $toolsDir); // "C:/localhost/Collector/Admin"
    $toolsDir .= '/Tools/';
    
    // if we are not in tools, the script wont start with the path to tools
    if ($toolsDir !== substr($script, 0, strlen($toolsDir))) {
        return false;
    }
    
    $toolPath = substr($script, strlen($toolsDir));
 // $toolPath = "GetData/index.php"
    
    // get path up to first directory separater (find the first folder name)
    $tool = substr($toolPath, 0, strpos($toolPath, '/'));
    
    return $tool;
}
