<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// existing database connection code below...
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'luxehome';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
