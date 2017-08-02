<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2016 Mikey Garcia & Nate Kornell


    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
 */
require 'Code/initiateCollector.php';


// get possible experiments to choose from
$experiments = array();
$exp_folder  = $FILE_SYS->get_path('Experiments');
foreach (get_Collector_experiments($FILE_SYS) as $exp_name) {
    $experiments[$exp_name] = "$exp_folder/$exp_name";
}

output_page_header($FILE_SYS, 'Collector Homepage');

$apps_folder  = $FILE_SYS->get_path('apps');


$apps = json_encode(array_slice(scandir($apps_folder), 2));
/*
foreach (get_Collector_experiments($FILE_SYS) as $exp_name) {
    $experiments[$exp_name] = "$exp_folder/$exp_name";
}
*/


if(isset($_SESSION['user_email'])){    
    $login_style = "display:none";
    $logout_style = '';
} else {
    $login_style = "";
    $logout_style = "display:none";
}

if(isset($_POST['skin'])){
    $_SESSION['skin'] = $_POST['skin']; 
}


if(!isset($_SESSION['skin'])){
    $_SESSION['skin'] = "Collector";
}


?>

<style>
.inlineUL { display: inline-block; margin: auto; text-align: left; }

.interface_div {
    display:none;
}
#error_message{
    font-size:100px;
    color: red;
}
#header_bar{
    position:fixed;
    right:0px;
    z-index:5;
    width:100%;
    background-color:white;
    top:0px;
    left:0px;
    padding: 10px;
    box-shadow: -1px 2px 5px grey;  
}
#username_input{
    width:400px;
}
#skin_span{
    position:absolute;
    left:10px;
}
#user_email_span{
    color:blue;
}
li{
    color:white;
}


</style>
<div id="header_bar" align="right">
    <table>
        <tr>
            <td>
                <form action="index.php" method="post">
                    <span id='skin_span'>
                        <span id="Collector_skin"   class="skin_button" value="Collector"></span>
                        <span id="CoLecture_skin"   class="skin_button" value="CoLecture"></span>
                        <span id="Apps_skin"        class="skin_button" value="Apps"></span>    
                    </span>
                </form>
            </td>
            <td>
                <span>
                    <span style="display:none"> Batteries of tasks ... or way to sub group tasks </span>
                    <span style="display:none"> other tools for allowing users to select courses etc. </span>
                </span>                            
            </td>
            <td>
                <form action="login.php" method="post">
                    <span id="login_register_span" style="<?= $login_style ?>">
                        <span id="username"></span>
                        <input id="username_input" name="user_email" type="email" placeholder="e-mail address">
                        <input id="password_input" name="user_password" type="password" placeholder="password">
                        <input type="submit" class="collectorButton" id="login_button" name='login_type' value="login">
                        <input type="button" class="collectorButton" id="register_button" value="register">
                        <input style="display:none" type="submit" class="collectorButton" id="register_submit" name='login_type' value="register">                        
                    </span>
                    <span id="logout_span" style="<?= $logout_style ?>">
                        <span id = "user_email_span">
                            <?php
                                if(isset($_SESSION['user_email'])){
                                    echo $_SESSION['user_email'];
                                }
                            ?>
                        </span>
                        <button type="submit" name="login_type" value="logout" class="collectorButton">Log out</button>
                    </span>
                </form>
            </td>
            <td>
                <button id='admin_button' class='collectorButton' style="<?= $logout_style ?>">Admin</button>
                <span style="color:white">-------</span><!-- laze fix for keeping content on screen -->
            </td>
        </tr>
    </table>
</div>

<div id="error_message">
    <?php
        if(isset($_SESSION['login_error'])){
            echo ($_SESSION['login_error']);
            unset($_SESSION['login_error']);
        }
    ?>
</div>

<script>
$("#admin_button").on("click",function(){
    window.location.href = '<?= $FILE_SYS->get_path('Admin') ?>';
});

$("#register_button").on("click",function(){
    // checks
    // password long enough?
    if($("#password_input").val().length<8){
        alert("Your password is too short. Please make a password of at least 8 characters. Ideally with a mixture of capital letters, numbers and characters");
    } else {
        confirm_password = prompt("Please confirm your password");
        if(confirm_password == $("#password_input").val()){
            $("#register_submit").click();
        } else {
            alert("The password you just wrote did not match the original password.");
        }
    }
    
});

</script>

<?php if(isset($_SESSION['user_email'])){ ?>

<div class="collectorRoot">

    <div id="Collector_div" class="interface_div">

        <h1>Collector</h1>
        <h2>A program for running experiments on the web</h2>

        <p>Welcome to the Collector. If you would like to begin an experiment,
           click on one of the links below.
        </p>
    
    
        <ul class="inlineUL">
          <?php foreach ($experiments as $name => $path): ?>
          <li><a href='<?= $path ?>'><?= $name ?></a></li>
          <?php endforeach; ?>
        </ul>
        
    </div>

    <div id="CoLecture_div" class="interface_div">
    
        here's where the CoLectures will be
        
    </div>
    
    <div id="Apps_div" class="interface_div">
        <h1>Apps</h1>
        <h2>Select an App to run it!</h2>
        
        <div id="apps_list_div">here's where the apps will be</div>
        
    </div>
        
</div>

<?php 
} 
?>

<script>

var current_skin = "<?= $_SESSION['skin'] ?>";

var apps = <?= $apps ?>;
console.dir(apps);

if(apps.length > 0){
    $("#apps_list_div").html("");
}
apps.forEach(function(element){
    $("#apps_list_div").append("<li><a href='Apps/"+element+"'>"+element+"</a></li>");
});


$(".skin_button").each(function(i,obj){
    console.dir(i);
    console.dir(obj.id);
    var this_value = obj.id.replace("_skin","");
    if(obj.id == current_skin+"_skin"){
        $("#"+obj.id).html("<em><b>"+this_value+"</b></em>"); 
        $("#"+this_value+"_div").show();
    } else {
        $("#"+obj.id).html("<input type='submit' name='skin' class='collectorButton' value='"+this_value+"'>");
    }
});

</script>



<?php
output_page_footer($FILE_SYS);
