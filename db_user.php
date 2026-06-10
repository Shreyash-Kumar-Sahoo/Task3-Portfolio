<?php
// db_user.php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'user_management_db';

$conn_user = mysqli_connect($host, $username, $password, $database);

if (!$conn_user) {
    die("Connection failed: " . mysqli_connect_error());
}
?>