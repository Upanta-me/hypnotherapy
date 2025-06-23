<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DB connection
    $conn = new mysqli("localhost", "root", "", "appointment");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create admin table if it doesn't exist
    $create_admin_table_sql = "CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_admin_table_sql)) {
        die("Error creating admin table: " . $conn->error);
    }

    // Check if admin exists, if not create default admin
    $check_admin_sql = "SELECT COUNT(*) as count FROM admin_users";
    $result = $conn->query($check_admin_sql);
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        // Create default admin user (username: admin, password: admin123)
        $default_username = "admin";
        $default_password = password_hash("admin123", PASSWORD_DEFAULT);
        
        $insert_admin_sql = "INSERT INTO admin_users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_admin_sql);
        $stmt->bind_param("ss", $default_username, $default_password);
        $stmt->execute();
        $stmt->close();
    }

    // Collect and sanitize inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    // Validate required fields
    if (empty($username) || empty($password)) {
        echo "Both username and password are required.";
        exit;
    }

    // Check admin credentials
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            // Password is correct, create session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            echo "success";
            exit;
        } else {
            echo "Invalid username or password.";
            exit;
        }
    } else {
        echo "Invalid username or password.";
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
    exit;
}
?>