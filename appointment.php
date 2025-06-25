<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DB connection
    $conn = new mysqli("localhost", "root", "", "appointment");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Create table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        service VARCHAR(100) NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_table_sql)) {
        die("Error creating table: " . $conn->error);
    }

    // Collect and sanitize inputs
    $fname = htmlspecialchars(trim($_POST['fname']));
    $lname = htmlspecialchars(trim($_POST['lname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $services = htmlspecialchars(trim($_POST['services']));
    $date = htmlspecialchars(trim($_POST['date']));
    $time = htmlspecialchars(trim($_POST['time']));

    // Validate required fields
    if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($services) || empty($date) || empty($time)) {
        echo "All fields are required.";
        exit;
    }

    // ✅ Check for valid time slot (within 9-5 except 1PM)
    $allowed_times = ["09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00"];
    if (!in_array($time, $allowed_times)) {
        echo "Selected time slot is not available.";
        exit;
    }

    // ✅ Limit to 2 appointments per email
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($email_count);
    $stmt->fetch();
    $stmt->close();

    if ($email_count >= 2) {
        echo "You have already booked the maximum number of appointments (2).";
        exit;
    }

    // ✅ Check if time slot is already booked for that day
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = ? AND appointment_time = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $date, $time);
    $stmt->execute();
    $stmt->bind_result($slot_count);
    $stmt->fetch();
    $stmt->close();

    if ($slot_count > 0) {
        echo "Selected time slot is already booked. Please choose another.";
        exit;
    }

    // ✅ Insert into DB
    $stmt = $conn->prepare("INSERT INTO appointments (first_name, last_name, email, phone, service, appointment_date, appointment_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sssssss", $fname, $lname, $email, $phone, $services, $date, $time);

    if ($stmt->execute()) {
        echo "Appointment booked successfully!";
    } else {
        echo "Error booking appointment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
