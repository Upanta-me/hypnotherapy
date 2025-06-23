<?php
$requestedDate = $_GET['date'];
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

$bookedSlots = [];
$sql = "SELECT appointment_time FROM appointment WHERE appointment_date = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $requestedDate);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bookedSlots[] = $row['appointment_time'];
}
$stmt->close();

$allSlots = [
    "09:00", "10:00", "11:00", "12:00", 
    "14:00", "15:00", "16:00" // 1:00 PM is break
];

$availableSlots = array_values(array_diff($allSlots, $bookedSlots));
echo json_encode($availableSlots);
?>