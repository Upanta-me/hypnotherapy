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

// Get completed appointments (past dates)
$completed_appointments = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE appointment_date < ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $completed_appointments = $row['total'];
}

// Get recent appointments
$recent_appointments = [];
$stmt = $conn->prepare("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_appointments[] = $row;
}

// Get appointment trends for the last 7 days
$trend_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE appointment_date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $trend_data[] = ['date' => $date, 'count' => $count];
}

$conn->close();
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-2"></i>
                Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! 
                Today is <?php echo date('l, F j, Y'); ?>
            </p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($total_appointments); ?>
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-chart-line me-1"></i>All time
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-calendar fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Today's Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $today_appointments; ?>
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-clock me-1"></i><?php echo date('M j, Y'); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success">
                                <i class="fas fa-calendar-day fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Upcoming (Next 7 Days)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $upcoming_appointments; ?>
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-arrow-right me-1"></i>Next week
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-info">
                                <i class="fas fa-calendar-alt fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Completed
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($completed_appointments); ?>
                            </div>
                            <div class="text-xs text-muted mt-1">
                                <i class="fas fa-check-circle me-1"></i>Past appointments
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-warning">
                                <i class="fas fa-check fa-2x text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity and Quick Actions -->
    <div class="row g-4">
        <!-- Recent Appointments -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock me-2"></i>Recent Appointments
                    </h6>
                    <div>
                        <a href="appointments.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-list me-1"></i>View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (count($recent_appointments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Service</th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_appointments as $appointment): ?>
                                        <?php 
                                        $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                        $is_past = strtotime($appointment_datetime) < time();
                                        $is_today = $appointment['appointment_date'] == date('Y-m-d');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3">
                                                        <?php echo strtoupper(substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">
                                                            <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                                        </div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($appointment['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    <?php echo htmlspecialchars($appointment['service']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($is_past): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Completed
                                                    </span>
                                                <?php elseif ($is_today): ?>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock me-1"></i>Today
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-calendar me-1"></i>Scheduled
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                                   class="btn btn-sm btn-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No appointments found</h6>
                            <p class="text-muted mb-0">Appointments will appear here once they are created.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="appointments.php" class="btn btn-primary">
                            <i class="fas fa-calendar-check me-2"></i>
                            Manage Appointments
                        </a>
                        <a href="settings.php" class="btn btn-success">
                            <i class="fas fa-cog me-2"></i>
                            System Settings
                        </a>
                        <a href="../index.html" class="btn btn-info" target="_blank">
                            <i class="fas fa-globe me-2"></i>
                            View Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Status
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-server me-2"></i>System Status
                    </h6>
                </div>
                <div class="card-body">
                    <div class="status-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Database Connection</span>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Online
                            </span>
                        </div>
                    </div>
                    <div class="status-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Website Status</span>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Active
                            </span>
                        </div>
                    </div>
                    <div class="status-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Last Updated</span>
                            <small class="text-muted"><?php echo date('g:i A'); ?></small>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>

<!-- Additional CSS for dashboard enhancements -->
<style>
.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.status-item {
    padding: 8px 0;
    border-bottom: 1px solid var(--border-color);
}

.status-item:last-child {
    border-bottom: none;
}

.table-hover tbody tr:hover {
    background-color: var(--light-bg);
}

.card-header h6 {
    color: white;
}

.bg-light {
    background-color: var(--light-bg) !important;
    border: 1px solid var(--border-color);
}
</style>

<?php require_once 'includes/footer.php'; ?> 