<?php
require_once 'includes/header.php';

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['flash_message'] = "Invalid appointment ID.";
    $_SESSION['flash_message_type'] = "danger";
    header("Location: appointments.php");
    exit;
}

$id = $_GET['id'];

// Connect to database
$conn = new mysqli("localhost", "root", "", "appointment");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch appointment details
$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $conn->close();
    $_SESSION['flash_message'] = "Appointment not found.";
    $_SESSION['flash_message_type'] = "danger";
    header("Location: appointments.php");
    exit;
}

$appointment = $result->fetch_assoc();

// Calculate appointment status
$appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
$is_past = strtotime($appointment_datetime) < time();
$is_today = $appointment['appointment_date'] == date('Y-m-d');
$is_tomorrow = $appointment['appointment_date'] == date('Y-m-d', strtotime('+1 day'));

$conn->close();
?>

<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Appointment Details</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-check me-2"></i>
                Viewing appointment #<?php echo $appointment['id']; ?>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="appointments.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash me-2"></i>Delete
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Appointment Details -->
        <div class="col-lg-8">
            <!-- Client Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user me-2"></i>Client Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Full Name</label>
                                <div class="info-value">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <?php echo strtoupper(substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">
                                                <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Email Address</label>
                                <div class="info-value">
                                    <a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>" class="text-decoration-none">
                                        <i class="fas fa-envelope me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($appointment['email']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Phone Number</label>
                                <div class="info-value">
                                    <a href="tel:<?php echo htmlspecialchars($appointment['phone']); ?>" class="text-decoration-none">
                                        <i class="fas fa-phone me-2 text-muted"></i>
                                        <?php echo htmlspecialchars($appointment['phone']); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="info-label">Service Requested</label>
                                <div class="info-value">
                                    <span class="badge bg-light text-dark p-2">
                                        <i class="fas fa-brain me-2"></i>
                                        <?php echo htmlspecialchars($appointment['service']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Appointment Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calendar-alt me-2"></i>Appointment Schedule
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="info-item">
                                <label class="info-label">Date</label>
                                <div class="info-value">
                                    <i class="fas fa-calendar me-2 text-muted"></i>
                                    <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label class="info-label">Time</label>
                                <div class="info-value">
                                    <i class="fas fa-clock me-2 text-muted"></i>
                                    <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label class="info-label">Status</label>
                                <div class="info-value">
                                    <?php if ($is_past): ?>
                                        <span class="badge bg-success p-2">
                                            <i class="fas fa-check me-1"></i>Completed
                                        </span>
                                    <?php elseif ($is_today): ?>
                                        <span class="badge bg-warning p-2">
                                            <i class="fas fa-clock me-1"></i>Today
                                        </span>
                                    <?php elseif ($is_tomorrow): ?>
                                        <span class="badge bg-info p-2">
                                            <i class="fas fa-calendar me-1"></i>Tomorrow
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary p-2">
                                            <i class="fas fa-calendar me-1"></i>Scheduled
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <label class="info-label">Booking Date</label>
                                <div class="info-value">
                                    <i class="fas fa-plus-circle me-2 text-muted"></i>
                                    Created on <?php echo date('F j, Y \a\t g:i A', strtotime($appointment['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Calendar Widget -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-calendar-day me-2"></i>Appointment Date
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="calendar-widget">
                        <div class="calendar-month">
                            <?php echo strtoupper(date('M', strtotime($appointment['appointment_date']))); ?>
                        </div>
                        <div class="calendar-day">
                            <?php echo date('d', strtotime($appointment['appointment_date'])); ?>
                        </div>
                        <div class="calendar-year">
                            <?php echo date('Y', strtotime($appointment['appointment_date'])); ?>
                        </div>
                        <div class="calendar-time">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                        </div>
                        <div class="calendar-day-name">
                            <?php echo date('l', strtotime($appointment['appointment_date'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Send Email
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($appointment['phone']); ?>" class="btn btn-success">
                            <i class="fas fa-phone me-2"></i>Call Client
                        </a>
                        <a href="appointments.php" class="btn btn-info">
                            <i class="fas fa-list me-2"></i>View All Appointments
                        </a>
                    </div>
                </div>
            </div>

            <!-- Time Information -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle me-2"></i>Time Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="time-info">
                        <?php if ($is_past): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                This appointment was completed.
                            </div>
                        <?php elseif ($is_today): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This appointment is scheduled for today!
                            </div>
                        <?php else: ?>
                            <?php 
                            $days_until = floor((strtotime($appointment['appointment_date']) - time()) / (60 * 60 * 24));
                            ?>
                            <div class="alert alert-info">
                                <i class="fas fa-calendar-alt me-2"></i>
                                This appointment is in <?php echo $days_until; ?> day<?php echo $days_until != 1 ? 's' : ''; ?>.
                            </div>
                        <?php endif; ?>
                        
                        <div class="time-details">
                            <small class="text-muted">
                                <strong>Created:</strong><br>
                                <?php echo date('M j, Y \a\t g:i A', strtotime($appointment['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete this appointment?</p>
                <div class="appointment-summary bg-light p-3 rounded">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar-circle me-3">
                            <?php echo strtoupper(substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($appointment['email']); ?></small>
                        </div>
                    </div>
                    <div class="appointment-details">
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?>
                            <br>
                            <i class="fas fa-clock me-1"></i>
                            <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                            <br>
                            <i class="fas fa-brain me-1"></i>
                            <?php echo htmlspecialchars($appointment['service']); ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <a href="appointments.php?delete=<?php echo $appointment['id']; ?>" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i>Delete Appointment
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Styles -->
<style>
.info-item {
    margin-bottom: 1.5rem;
}

.info-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-secondary);
    margin-bottom: 8px;
    display: block;
}

.info-value {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
}

.calendar-widget {
    max-width: 200px;
    margin: 0 auto;
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.calendar-month {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    padding: 12px 0;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 1px;
}

.calendar-day {
    font-size: 3rem;
    font-weight: 700;
    padding: 20px 0;
    color: var(--primary-color);
    background: white;
}

.calendar-year {
    border-top: 1px solid var(--border-color);
    padding: 8px 0;
    font-size: 14px;
    color: var(--text-secondary);
    background: var(--light-bg);
}

.calendar-time {
    background: var(--secondary-color);
    color: white;
    padding: 12px 0;
    font-weight: 600;
    font-size: 16px;
}

.calendar-day-name {
    background: var(--light-bg);
    color: var(--text-secondary);
    padding: 8px 0;
    font-size: 13px;
    font-weight: 500;
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
    flex-shrink: 0;
}

.badge {
    font-size: 12px;
    font-weight: 500;
}

.alert {
    border: none;
    border-radius: var(--border-radius);
    font-size: 14px;
}

.time-details {
    padding-top: 12px;
    border-top: 1px solid var(--border-color);
    margin-top: 12px;
}

.appointment-summary {
    border: 1px solid var(--border-color);
}

.appointment-details {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid var(--border-color);
}

@media (max-width: 768px) {
    .d-flex.gap-2 {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .calendar-widget {
        max-width: 150px;
    }
    
    .calendar-day {
        font-size: 2.5rem;
        padding: 15px 0;
    }
    
    .info-item {
        margin-bottom: 1rem;
    }
}

/* Enhanced button animations */
.btn:hover {
    transform: translateY(-1px);
}

.btn:active {
    transform: translateY(0);
}

/* Card hover effects */
.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
</style>

<?php require_once 'includes/footer.php'; ?> 