<?php
    $addedScripts = array(
        $_PATH->get('Sha256 JS'),
        $_PATH->get('root') . '/Admin/Login/Login.js'
    );
    
    $title = 'Collector - Login';
    
    require $_PATH->get('Header');
    
    echo $loginResult;
?>

<div id="PaswordInputArea">
    Please enter your password. <br>
    <input type="password" required id="PasswordInput"> 
    <button type="button" id="PasswordSubmitButton">Submit</button>
</div>

<script>
    var hashIterations =  <?= $hashIterations    ?>;
    var nonce          = "<?= $_SESSION['nonce'] ?>";
</script>

<?php
    require $_PATH->get('Footer');
