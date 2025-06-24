<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Hardcoded credentials (in a real app, this would be in a database with hashed passwords)
$admin_username = 'admin';
$admin_password = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate current password
    if ($current_password !== $admin_password) {
        $_SESSION['flash_message'] = "Current password is incorrect.";
        $_SESSION['flash_message_type'] = "danger";
    } 
    // Validate new password
    elseif (strlen($new_password) < 6) {
        $_SESSION['flash_message'] = "New password must be at least 6 characters.";
        $_SESSION['flash_message_type'] = "danger";
    }
    // Confirm passwords match
    elseif ($new_password !== $confirm_password) {
        $_SESSION['flash_message'] = "New passwords do not match.";
        $_SESSION['flash_message_type'] = "danger";
    }
    else {
        // In a real application, you would update the password in the database
        // Here we'll just simulate success
        
        // For a real application with database:
        // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        // $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
        // $stmt->bind_param("ss", $hashed_password, $_SESSION['admin_username']);
        // $stmt->execute();
        
        $_SESSION['flash_message'] = "Password changed successfully!";
        $_SESSION['flash_message_type'] = "success";
    }
    
    header('Location: settings.php');
    exit;
}

// If someone tries to access directly without POST data, redirect to settings
header('Location: settings.php');
exit; 