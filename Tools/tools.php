<?php
    session_start();

    // set the root for initial file locations
    $_root = '..';

    // automatically load classes when they are needed
    function autoClassLoader($className) {
        $root = '';
        $ancestors = 0;
        while (!is_dir("{$root}Code/classes") AND ($ancestors < 3)) {
            $root .= "../";
            ++$ancestors;
        }
        $loc = "{$root}Code/classes/$className.class.php";
        if (is_file($loc)) {
            require $loc;
        } else {
            var_dump(scandir(dirName($loc)));
            echo "Object $className is not found";
        }
    }
    spl_autoload_register("autoClassLoader");
    
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
    <?php
        $_PATH->stylesheet("Global CSS");
        $_PATH->stylesheet("Tools CSS");
        $_PATH->script("Jquery");
        $_PATH->script("Sha256 JS");
        $_PATH->script("Login JS");
    ?>
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
    $state = loginState($_SETTINGS->password);
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
              echo "<option value='$tool' class='go'><b>$tool</b></option>";
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
        <script type="text/javascript">
            $(".go").click(function(){
                $("#toolSelector").submit();
            });
        </script>

        <form id="toolSelector" action="" method="post"></form>
	</body>
</html>
