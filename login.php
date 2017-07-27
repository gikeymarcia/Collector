<?php

require 'Code/initiateCollector.php';

$servername     = "localhost";
$username       = "anthony";
$password       = "HVIg1Xg6XChmYb33";
$database_name  = "All_Users";

// Create connection
$conn = new mysqli($servername, $username, $password, $database_name);

/* ---------------------- SECURITY CHECKS HERE!!! ---------------------- */

$user_email = $_POST['user_email'];
$user_password = $_POST['user_password'];


// is the person registering?
if($_POST["login_type"]=="register"){
    $sql = "INSERT INTO `users` ( `email`, `password`) VALUES('$user_email', '$user_password')";

    if ($conn->query($sql) === TRUE) {
        $success_fail = "success"; 
    } else {
        echo "Error adding user: " . $conn->error;
    }
}

// is the user logging in
if($_POST["login_type"]=="login"){
    
    $sql="SELECT password FROM users WHERE email='anthony.haffey@reading.ac.uk'"; // "WHERE email='".$user_email."' LIMIT 1;
    
    $result = $conn->query($sql);
    
    if($result->num_rows>1){
        // something has gone very wrong
    } else {
        
        $row = mysqli_fetch_array($result);
        $actual_password = $row['password'];
        if($actual_password == $user_password){
            $success_fail = "success";
        }
    }    
}


if($success_fail == "success"){
    $_SESSION['user_email'] = $user_email;
    //$_SESSION['login_time'] = "blah"; // set time, and then GIVE USERS A 10 minute warning!!
    header("Location:index.php");
}
    
    // sql to create table
/*
    $sql = "CREATE TABLE Users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        email VARCHAR(50),
        password VARCHAR(50),
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
    
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    */
    
    
    /*
    
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


// if login successfull




?>