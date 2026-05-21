<?php
// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // e.g., root
define('DB_PASSWORD', 'Vishnu@2003'); // e.g., '' for no password
define('DB_NAME', 'user_registration');

// Attempt to connect to MySQL database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>