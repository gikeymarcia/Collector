<!DOCTYPE html>
<html>
<head>
    <style>
        body { overflow: hidden; }
        .outer { display: table; width: 100%; height: 100%; position: absolute; }
        .inner { display: table-cell; text-align: center; vertical-align: middle; }
        .Main { display: inline-block; margin-bottom: 100px; }
        .Main * { margin: 4px; }
        .invis { visibility: hidden; }
        .Error { color: #B00; font-size: 90%; }
    </style>
</head>
<body>
    <div class="outer">
        <div class="inner">
            <div class="Main">
                <div class="Welcome">Welcome to GetData</div>
                <div>Please enter the password below.</div>
                <form method="post" >
                    <input type="password" name="Password" autocomplete="off" autofocus="autofocus" /><br />
                    <input type="submit" value="Submit" />
                </form>
                <?php
                    if( isset( $_POST['Password'] ) ) {
                        $style = 'Error';
                    } else {
                        $style = 'Error invis" ';
                    }
                ?>
                <div class="<?= $style ?>" >Incorrect password</div>
            </div>
        </div>
    </div>
</body>