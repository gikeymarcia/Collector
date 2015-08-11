<?php
    require 'fileLocations.php';     // sends file to the right place

    // get previous page to simulate a back button
    $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link href="css/global.css" rel="stylesheet" type="text/css" />
    <title>Javascript not enabled</title>
</head>
<body>
    <div class="nojs">
        <img src="css/nojswarning.png" alt="No javascript warning" />
        <div class="nojs-text">
            <h3>JavaScript is not activated.</h3>
            <p>JavaScript must be enabled in order for you to use this site. However, it
               seems JavaScript is either disabled or not supported by your browser.</p>
            <p>Enable JavaScript <a href="http://enable-javascript.com/" target="_blank">by changing your
               browser options</a>, then <a href="<?php echo $url; ?>">return to the login page</a> to try again. Thank you!</p>
       </div>
    </div>
</body>
</html>
