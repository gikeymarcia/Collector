<?php
function getTools() {
    $toolsDir = __DIR__ . '/Tools';
    $tools    = scandir($toolsDir);

    foreach ($tools as $i => $tool) {
        if (   $tool === '.'
            || $tool === '..'
            || !is_dir("$toolsDir/$tool")
            || !is_file("$toolsDir/$tool/index.php")
            || strtolower($tool) === 'sample'
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

function verifyLogin($password) {
    if (isset($_SESSION['admin']['login'])) {
        if (time() > $_SESSION['admin']['login']) {
            unset($_SESSION['admin']['login']);
        }
    }

    // check if we have logged in
    if (!isset($_SESSION['admin']['login'])) {
        // haven't logged in, run password script
        require __DIR__ . '/LoginFunctions.php';
        runLogin($password);
    }
}

function writeToolsHtmlHead(FileSystem $_files, $tool = null) {
    if ($tool === null) $tool = determineCurrentTool();

    $title = $tool ? $tool : 'Collector - Admin Menu';
    require $_files->get_path('Header');

    $rootUrl = $_files->get_path('root', 'url');

    $adminStyle = "$rootUrl/Admin/adminStyle.css";
    echo "<link rel='stylesheet' href='$adminStyle'>";

    $adminJS = "$rootUrl/Admin/adminScript.js";
    echo "<script src='$adminJS'></script>";
}

function writeToolsNavBar(FileSystem $_files, $tool = null) {
    $tool      = ($tool !== null) ? $tool : determineCurrentTool();
    $tools     = getTools();
    $logoutUrl = $_files->get_path('root') . '/Admin/logout.php';
    $title     = $tool ? $tool : 'Collector - Admin Menu';

    $options = '';

    if ($tool === false) {
        $options .= '<option selected disabled hidden value="">Choose a tool</option>';
    }

    $rootUrl = $_files->get_path('root', 'url');

    foreach ($tools as $toolOption) {
        $selected = ($tool === $toolOption) ? 'selected' : '';
        $path     = "$rootUrl/Admin/Tools/$toolOption";

        $options .= "<option $selected value='$path'>$toolOption</option>";
    }

    ?>
    <div id="ToolsNavBar">
        <h1><?= $title ?></h1>
        <a id="LogOut" href="<?= $logoutUrl ?>">Logout</a>
        <select name="CollectorToolSelection" class="collectorInput" id="CollectorToolSelection">
            <?= $options ?>
        </select>
    </div>
    <?php
}
