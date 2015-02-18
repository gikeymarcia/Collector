<?php
    require '../Code/fileLocations.php';
    require $up . $codeF . 'CustomFunctions.php';
    require $up . $expFiles . 'Settings.php';
    require 'loginFunctions.php';
    session_start();
?>

<!DOCTYPE HTML>
<head>
    <link href="../Code/css/global.css"  rel="stylesheet"   type="text/css"/>
    <script src="../Code/javascript/jquery-1.10.2.min.js"   type="text/javascript"></script>
    <script src="../Code/javascript/sha256.js"              type="text/javascript"></script>
    <script src="../Code/javascript/loggingIn.js"           type="text/javascript"></script>
    
    <title>Collector Tools</title>
</head>
<html>
	<body>
    
<?php
    $state = loginState($Password);
    if ($state != 'loggedIn') {
        LoginPrompt($state);
        $_SESSION['admin'] = array(
            'status' => 'attempting',
            'birth'  => time()
        );
        exit;
    }
    echo '<div id="nav">' .
            '<h1>Welcome to Collector Tools</h2>' .
            '<a id="logout" href=logOut.php>Logout</a>' .
         '</div>'
?>
	</body>
</html>