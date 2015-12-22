<?php

session_start();

// set the root for initial file locations (used in other tools scripts)
$_root = '..';

/**
 * Class autoloader.
 *
 * @param string $className The class to load.
 */
function autoClassLoader($className)
{
    $root = '';
    $ancestors = 0;
    while (!is_dir("{$root}Code/classes") and ($ancestors < 3)) {
        $root .= '../';
        ++$ancestors;
    }
    $loc = "{$root}Code/classes/$className.php";
    if (is_file($loc)) {
        require $loc;
    } else {
        var_dump(scandir(dirName($loc)));
        echo "Object $className is not found";
    }
}
spl_autoload_register('autoClassLoader');

// load file locations
$_PATH = new Pathfinder();

// load custom functions
require $_PATH->get('Custom Functions');
require 'loginFunctions.php';

// load configs
$_SETTINGS = getCollectorSettings();

// declaring admin for first login
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = array();
}
$admin = &$_SESSION['admin'];


/*
 * Display
 */

// if user has selected a tool, provide it
require 'toolsFunctions.php';
$tools = getTools();
$userChoice = filter_input(INPUT_POST, 'tool', FILTER_SANITIZE_STRING);
if ($userChoice !== null) {
    // if the tool being asked for exists save it
    if (isset($tools[$userChoice])) {          
        $admin['tool'] = $userChoice;
        $admin['heading'] = $userChoice;
    }
    
    // go back to root of current folder (tools home)
    header('Location: ./');                     
}

if (!isset($admin['tool'])) {
    $admin['heading'] = 'Collector Tools';
}

?>

<!DOCTYPE HTML>
<html>
<head>
  <meta charset="utf-8">
  <title>Collector Tools -- <?= $admin['heading'] ?></title>
    
  <!-- Icons -->
  <link rel="icon" href="../Code/icon.png" type="image/png">
  
  <!-- Base stylesheets -->
  <?= $_PATH->getStylesheetTag('Global CSS') ?>
  <?= $_PATH->getStylesheetTag('Tools CSS') ?>
  <!-- Do not use flexbox settings from the rest of the experiment -->
  <style type="text/css">
    #flexBody, html {
      display: block;
      height: auto;
    }
  </style>
  
  <!-- Base scripts -->
  <?= $_PATH->getScriptTag('Jquery') ?>
  <?= $_PATH->getScriptTag('Sha256 JS') ?>
  <?= $_PATH->getScriptTag('Login JS') ?>
</head>

<body id="flexBody">

<!-- login prompt -->
<?php
// handle login state and display of login prompt
$state = loginState($_SETTINGS->password);
if ($state !== 'loggedIn') {
    loginPrompt($state);
    $admin['status'] = 'attempting';
    $admin['birth'] = time();
    exit;
}
?>
    
<!-- welcome bar at the top -->
<div id="nav">
  <h1><?= $admin['heading'] ?></h1>
  <a id="logout" href="logOut.php">Logout</a>

  <div>
    <!-- tool selector dropdown -->
    <select name="tool" form="toolSelector" class="toolSelect collectorInput">
      <option value="" selected="true">Choose a tool</option>
      <?php foreach ($tools as $tool => $location): ?>
      <option value='<?= $tool ?>' class='go'><b><?= $tool ?></b></option>
      <?php endforeach; ?>
    </select>

    <button type="submit" form="toolSelector" class="collectorButton">Go!</button>
  </div>
</div>

<!-- current tool -->    
<?php   
// require the selected tool
if (isset($admin['tool'])) {
    // key within session where data can be stored
    $dataHolderKey = $admin['tool'].'Data';       

    // if we haven't made a data holder yet, make it
    if (!isset($admin[$dataHolderKey])) {           
        $admin[$dataHolderKey] = array();          
    }

    // make alias for each tool to access its session data
    $_DATA = &$admin[$dataHolderKey];               
    require_once $tools[$admin['tool']];
}
?>

<!-- form submission -->
<form id="toolSelector" action="" method="post"></form>
<script type="text/javascript">
  $(".go").click(function(){
    $("#toolSelector").submit();
  });
</script>

</body>
</html>
