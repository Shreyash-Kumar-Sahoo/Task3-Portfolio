<?php
// API endpoint to securely check if an email exists in the database
header('Content-Type: application/json');
require 'db_user.php';

$data = json_decode(file_get_contents('php://input'), true);
$email_to_check = isset($data['email']) ? trim($data['email']) : '';

$response = ['exists' => false];

if (!empty($email_to_check) && filter_var($email_to_check, FILTER_VALIDATE_EMAIL)) {
    $sql = "SELECT id FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn_user, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email_to_check);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $response['exists'] = true;
        }
        mysqli_stmt_close($stmt);
    }
}

echo json_encode($response);
?>