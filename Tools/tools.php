<?php
    require '../Code/fileLocations.php';
    require $up . $codeF . 'CustomFunctions.php';
    require $up . $expFiles . 'settings.php';
    require 'loggingIn.php';
    require 'toolFunctions.php';
    session_start();
?>

<!DOCTYPE HTML>
<head>
    <link href="../Code/css/global.css"  rel="stylesheet"   type="text/css"/>
    <script src="../Code/javascript/jquery-1.10.2.min.js"   type="text/javascript"></script>
    <script src="../Code/javascript/loggingIn.js"           type="text/javascript"></script>
    <script src="../Code/javascript/sha256.js"              type="text/javascript"></script>
    
    <title>Collector Tools</title>
</head>
<html>
	<body>
    
<?php
    $state = loginState($Password);
    // $state = 'challengeFail';
    $pagetext = '';
    switch ($state) {
        case 'setPassword':
            echo 'set pass';
            $pagetext .=
                '<div id="Error">' . 
                    '<h2>You are not allowed to use <em>Tools</em> until you have set a password</h2>' . 
                    '<p> The password can be set within <em>Experiment/settings.php<em></p>' . 
                 '</div>';
            break;
        case 'challengeFail':
            echo 'wrong password';
            $pagetext .= '<p class="error">Not so fast. That isn\'t the right password</p>';
        case 'returning':
            echo 'returning visitor' . '<br>';
        case 'newVisitor':
            // echo 'new visitor';
            echo '<div id="login">
                      Password:<input type="password" id="pass"></input>
                      <input id="fauxSubmit" type="submit" value="Submit"></input>
                      <form id="hashSubmit" action="tools.php" method="post">
                          <span id="nonce">' . $_SESSION['challenge']['NONCE'] . '</span>
                          <input id="realInput" name="response" type="text"></input>
                      </form>
                  </div>';
            // exit;
            break;
        case 'loggedIn':
            echo "<h2>Logged in </h2>";
            echo '<input type="submit" value="log out"></input>';
            // continue to the rest of the page
            break;
        case 'unknownState':
            echo '<p>We have no idea how you got here.
                     Post this as an issue on the <a href="http://www.github.com/gikeymarcia/collector">project Github Page</a>.
                  </p>';
            break;
        default:
            echo '<h2><a href=".?restart=TRUE">Log out</a></h2>';
            break;
    }
    echo $pagetext;
    if ($state != 'loggedIn') {
        exit;
    }
    
   
?>
	</body>
</html>