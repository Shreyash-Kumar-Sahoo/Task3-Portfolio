<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role_name'] !== 'Admin') {
    header("location: login.php");
    exit;
}
// admin_delete.php
include('db_user.php');

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $user_id = intval($_GET['id']);
    
    $sql = "DELETE FROM users WHERE id = ?";
    
    if ($stmt = mysqli_prepare($conn_user, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: admin.php?msg=deleted");
            exit();
        } else {
            echo "Error: Could not execute query. " . mysqli_error($conn_user);
        }
        mysqli_stmt_close($stmt);
    }
} else {
    echo "Invalid request. User ID is missing.";
}
?>