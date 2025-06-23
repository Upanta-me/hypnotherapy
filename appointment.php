<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection setup
    $servername = "localhost";
    $username = "root";
    $password = ""; // 
    $dbname = "appointment";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check DB connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Collect and sanitize POST data
    $fname = htmlspecialchars(trim($_POST['fname']));
    $lname = htmlspecialchars(trim($_POST['lname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $services = htmlspecialchars(trim($_POST['services']));
    $date = htmlspecialchars(trim($_POST['date']));

    // Optional: basic validation
    if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($services) || empty($date)) {
        echo "All fields are required.";
        exit;
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO appointments (first_name, last_name, email, phone, service, appointment_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fname, $lname, $email, $phone, $services, $date);

    if ($stmt->execute()) {
        echo "Appointment booked successfully!";
        
        // ✅ Send confirmation email
        //$to = $email;
        //$subject = "Appointment Confirmation";
        //$message = "Hello $fname $lname,\n\nThank you for booking a $services session on $date.\nWe’ll contact you soon.\n\n- Yoga Studio Team";
        //$headers = "From: no-reply@yogastudio.com";

        // Use @mail to avoid warnings if mail fails
       // @mail($to, $subject, $message, $headers);
        
    } else {
        echo "Error booking appointment: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
