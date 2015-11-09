<?php
    session_start();

    // set the root for initial file locations
    $_root = '..';
    
    // load file locations
    require $_root.'/Code/pathfinder.class.php';
    $_PATH = new Pathfinder();
    
    // load configs
    require $_PATH->get('Parse');
    $_CONFIG = Parse::fromConfig($_PATH->get('Config'), true);
    
    // load custom functions
    require $_PATH->get('Custom Functions');
    require 'loginFunctions.php';
    
    // declaring admin for first login
    if (!isset($_SESSION['admin'])) {
        $_SESSION['admin'] = array();
    }
    $admin =& $_SESSION['admin'];
    
    // scanning for available tools
    require 'toolsFunctions.php';
    $tools = getTools();                            // return array of tools
    if(isset($_POST['tool'])) {                     // if they are asking for a tool
        if (isset($tools[ $_POST['tool'] ])) {          // if the tool being asked for exists
            $admin['tool'] = $_POST['tool'];                // save tool selection
            $admin['heading'] = $_POST['tool'];
        }
        header('Location: ./');                     // go back to root of current folder
    }
    
    if (!isset($admin['tool'])) {
        $admin['heading'] = 'Collector Tools';
    }
?>

<!DOCTYPE HTML>
<head>
    <link rel="icon" href="../Code/icon.png" type="image/png">
    <link href="../Code/css/global.css"  rel="stylesheet"   type="text/css"/>
    <script src="../Code/javascript/jquery.js"   type="text/javascript"></script>
    <script src="../Code/javascript/sha256.js"              type="text/javascript"></script>
    <script src="../Code/javascript/loggingIn.js"           type="text/javascript"></script>
    
    <title>Collector Tools -- <?= $admin['heading'] ?></title>
</head>
<html>
    <style type="text/css">
        /*Do not use flexbox settings from the rest of the experiment*/
        #flexBody, html {
            display: block;
            height: auto;
        }
    </style>
	<body id="flexBody">
<?php
    // handling login state and display of login prompt
    $state = loginState($_CONFIG->password);
    if ($state != 'loggedIn') {
        LoginPrompt($state);
        $admin['status'] = 'attempting';
        $admin['birth']  = time();
        exit;
    }
?>
        <!-- displaying the welcome bar at the top -->
        <div id="nav">
            <h1><?= $admin['heading'] ?></h1>
             
            <a id="logout" href="logOut.php">Logout</a>
            <div>
                <!-- showing tool selector dropdown -->
                <select name="tool" form="toolSelector" class="toolSelect collectorInput">
                    <option value="" selected="true">Choose a tool</option>
<?php      foreach ($tools as $tool => $location) {
              echo '<option value="' . $tool . '"><b>' . $tool . '</b></option>';
            }
?>              </select>
                <button type="submit" form="toolSelector" class="collectorButton">Go!</button>
            </div>
        </div>

        
<?php   // require the selected tool
        if (isset($admin['tool'])) {

            $dataHolderKey = $admin['tool'] . 'Data';       // key within session where data can be stored
            if (!isset($admin[$dataHolderKey])) {           // if we haven't made a data holder yet
                $admin[$dataHolderKey] = array();               // make it
            }
            $_DATA =& $admin[$dataHolderKey];               // make alias for each tool to access it's session data
            require_once $tools[$admin['tool']];
        }
?>
        

        <form id="toolSelector" action="" method="post"></form>
	</body>
</html>
