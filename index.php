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



    $servername     = "localhost";
    $username       = "anthony";
    $password       = "HVIg1Xg6XChmYb33";
    $database_name  = "Collector_Users";

    // Create connection
    $conn = new mysqli($servername, $username, $password,$database_name);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    
    
    
    /*
    
    // sql to create table
    $sql = "CREATE TABLE Users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    email VARCHAR(50),    
    reg_date TIMESTAMP
    )";

    
    
    if ($conn->query($sql) === TRUE) {
        echo "Table  created successfully";
    } else {
        echo "Error creating table: " . $conn->error;
    }
    $conn->close();
    */
    
    
    /*
    // Create database
    $sql = "CREATE DATABASE $database_name";
    if ($conn->query($sql) === TRUE) {
        echo "Database created successfully";
    } else {
        echo "Error creating database: " . $conn->error;
    }
 
    $conn->close();
   */


if(isset($_SESSION['username'])){    
    print_r($_SESSION['username']);
    $login_style = "display:none";
} else {
    $login_style = "";
}

// get possible experiments to choose from
$experiments = array();
$exp_folder  = $FILE_SYS->get_path('Experiments');
foreach (get_Collector_experiments($FILE_SYS) as $exp_name) {
    $experiments[$exp_name] = "$exp_folder/$exp_name";
}

output_page_header($FILE_SYS, 'Collector Homepage');


?>

<style>
  .inlineUL { display: inline-block; margin: auto; text-align: left; }
  #header_bar{
    position:fixed;
    right:0px;
    z-index:5;
    width:100%;
    background-color:white;
    top:0px;
    left:0px;
    padding: 10px;
    border-bottom-style: solid;
    border-bottom-width: 2px;
    border-bottom-color: blue;
    box-shadow: -1px 2px 5px grey;  
  }
  #username_input{
      width:400px;
  }

</style>
<form action="index.php" method="post">
    <div id="header_bar" align="right">
        <span id="username"></span>
        <input id="username_input" type="email" placeholder="e-mail as username (may not be necessary)">
        <input type="button" id="register_button" value="register" style="<?= $login_style ?>">
        <input type="button" id="register_button" value="login" style="<?= $login_style ?>">
        <span style="color:white">-----</span><!-- laze fix for keeping content on screen -->
    </div>
</form>


<div class="collectorRoot">
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

    <p>Otherwise, you can access one of the other tools
       <a href="<?= $FILE_SYS->get_path('Admin') ?>">here</a>.
    </p>
</div>

<?php
output_page_footer($FILE_SYS);
