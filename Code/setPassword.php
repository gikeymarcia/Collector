<?php
    // if the key doesn't exist OR it isn't set to true then reject their action
    if (!isset($_SESSION)
    ) {
        exit('You are not allowed to access this script directly. To change your
              password delete the password file in "Experiments/_Common/".');
    }

    if (isset($_POST['pass'])) {
        $_SETTINGS->set_password($_POST['pass']);
        if ($_SETTINGS->password !== null) {
            $noPass = false;
            $root = $FILE_SYS->get_path("Root");
            header("Location: $root");
            exit;
        }
    }

    $title = "Initial Setup";
    output_page_header($FILE_SYS);

 ?>

<form action="" method="post" accept-charset="utf-8" id="content">
    <h3>Password:</h3>
    <input type="password" name="pass" value="" placeholder="3 characters or more" class="collectorInput" autofocus="">
    <button type="submit" class="collectorButton">Set Password</button>
    <p>
        <i>Note: To reset your password delete the password file in
        <code>Experiments/_Common/</code></i>
    </p>
    <p><b>Password must be 3 characters or longer</b></p>
</form>

<style type="text/css">
  #content {
    width: 400px;
  }
  p {
    text-align: center;
    margin-top: 1.5em;
  }
  body {
    font-size: 1.1em;
  }
</style>


<?php output_page_footer($FILE_SYS); exit;?>
