<?php

session_start();
unset($_SESSION['admin']);

// go back to root of current folder
header('Location: ./');
