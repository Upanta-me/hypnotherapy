<?php
$requestedDate = $_GET['date'];
$conn = new mysqli("localhost", "root", "", "appointment");

$bookedSlots = [];
$sql = "SELECT appointment_time FROM appointments WHERE appointment_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $requestedDate);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bookedSlots[] = $row['appointment_time'];
}
$stmt->close();

$allSlots = [
    "09:00 AM", "10:00 AM", "11:00 AM", "12:00 PM", 
    "02:00 PM", "03:00 PM", "04:00 PM" // 1:00 PM is break
];

$availableSlots = array_values(array_diff($allSlots, $bookedSlots));
echo json_encode($availableSlots);
