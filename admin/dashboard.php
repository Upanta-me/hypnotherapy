<?php
require_once 'includes/header.php';

// Connect to database
$conn = new mysqli("localhost", "root", "", "appointment");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get total appointments
$total_appointments = 0;
$result = $conn->query("SELECT COUNT(*) as total FROM appointments");
if ($result && $row = $result->fetch_assoc()) {
    $total_appointments = $row['total'];
}

// Get today's appointments
$today = date('Y-m-d');
$today_appointments = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE appointment_date = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $today_appointments = $row['total'];
}

// Get upcoming appointments (next 7 days)
$next_week = date('Y-m-d', strtotime('+7 days'));
$upcoming_appointments = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE appointment_date > ? AND appointment_date <= ?");
$stmt->bind_param("ss", $today, $next_week);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $upcoming_appointments = $row['total'];
}

// Get recent appointments
$recent_appointments = [];
$stmt = $conn->prepare("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_appointments[] = $row;
}

$conn->close();
?>

<div class="container-fluid py-4">
    <h1 class="h2 mb-4">Dashboard</h1>
    
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_appointments; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Appointments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $today_appointments; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Upcoming (Next 7 Days)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $upcoming_appointments; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Recent Appointments</h6>
                    <a href="appointments.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_appointments) > 0): ?>
                                    <?php foreach ($recent_appointments as $appointment): ?>
                                        <tr>
                                            <td><?php echo $appointment['id']; ?></td>
                                            <td><?php echo $appointment['first_name'] . ' ' . $appointment['last_name']; ?></td>
                                            <td><?php echo $appointment['service']; ?></td>
                                            <td><?php echo $appointment['appointment_date']; ?></td>
                                            <td><?php echo $appointment['appointment_time']; ?></td>
                                            <td>
                                                <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No appointments found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 