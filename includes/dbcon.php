<?php
date_default_timezone_set('Asia/Manila');
// //Localhost
// $servername = "localhost";
// $username = "root";
// $password = "";
// PRODUCTION CONNECTION
$servername = "172.30.5.8";
$username = "root";
$password = "@@3metree6@@";
$dbname = "db_recievingsystem";
$date = date("Y-m-d");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection FIRST
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Now it's safe to call methods on $conn
$conn->set_charset("utf8mb4");

// Memory and upload limits
ini_set('memory_limit', '256M');
ini_set('post_max_size', 500 * 1024 * 1024); // 500M in bytes
ini_set('upload_max_filesize', 500 * 1024 * 1024); // 500M in bytes
