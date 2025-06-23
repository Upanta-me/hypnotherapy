<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit();
}

// Check if appointment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-panel.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// DB connection
$conn = new mysqli("localhost", "root", "", "appointment");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get appointment details
$sql = "SELECT * FROM appointments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();
$conn->close();

// If appointment not found, redirect
if (!$appointment) {
    header("Location: admin-panel.php");
    exit();
}

// Service names mapping
$services = [
    'consultancy' => 'Consultancy',
    'per_session_hypnotherapy' => 'Per Session Hypnotherapy',
    'past_life_regression' => 'Past Life Regression',
    'inner_child_healing' => 'Inner Child Healing',
    'womb_healing' => 'Womb Healing',
    'age_regression' => 'Age Regression',
    'theta_healing' => 'Theta Healing'
];

$service_name = $services[$appointment['service']] ?? $appointment['service'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointment #<?php echo $appointment['id']; ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .admin-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .appointment-card {
            background: white; border-radius: 15px; padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem;
        }
        .info-row {
            border-bottom: 1px solid #eee; padding: 1rem 0;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            font-weight: 600; color: #667eea; margin-bottom: 0.5rem;
        }
        .info-value {
            font-size: 1.1rem; color: #333;
        }
        .status-badge {
            font-size: 0.9rem; padding: 0.5rem 1rem;
            border-radius: 25px; font-weight: 600;
        }
        .status-upcoming { background-color: #e3f2fd; color: #1976d2; }
        .status-today { background-color: #e8f5e8; color: #2e7d32; }
        .status-past { background-color: #fafafa; color: #616161; }
        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; color: white; padding: 0.75rem 1.5rem;
            border-radius: 25px; text-decoration: none;
            transition: transform 0.2s;
        }
        .back-btn:hover {
            transform: translateY(-2px); color: white;
        }
        .action-buttons .btn {
            margin-right: 0.5rem; margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-calendar-check me-2"></i>Appointment Details</h2>
                    <p class="mb-0">Viewing appointment #<?php echo $appointment['id']; ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="admin-panel.php" class="back-btn">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <div class="appointment-card">
            <div class="row mb-3">
                <div class="col-md-8">
                    <h3 class="text-primary mb-0">
                        <i class="fas fa-user me-2"></i>
                        <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                    </h3>
                </div>
                <div class="col-md-4 text-end">
                    <?php
                    $appointment_date = strtotime($appointment['appointment_date']);
                    $today = strtotime('today');
                    
                    if ($appointment_date > $today) {
                        echo '<span class="status-badge status-upcoming"><i class="fas fa-clock me-1"></i>Upcoming</span>';
                    } elseif ($appointment_date == $today) {
                        echo '<span class="status-badge status-today"><i class="fas fa-calendar-day me-1"></i>Today</span>';
                    } else {
                        echo '<span class="status-badge status-past"><i class="fas fa-history me-1"></i>Past</span>';
                    }
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">
                            <i class="fas fa-envelope me-2"></i>Email Address
                        </div>
                        <div class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>">
                                <?php echo htmlspecialchars($appointment['email']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">
                            <i class="fas fa-phone me-2"></i>Phone Number
                        </div>
                        <div class="info-value">
                            <a href="tel:<?php echo htmlspecialchars($appointment['phone']); ?>">
                                <?php echo htmlspecialchars($appointment['phone']); ?>
                            </a>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">
                            <i class="fas fa-concierge-bell me-2"></i>Service Requested
                        </div>
                        <div class="info-value">
                            <strong><?php echo htmlspecialchars($service_name); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-row">
                        <div class="info-label">
                            <i class="fas fa-calendar-alt me-2"></i>Appointment Date
                        </div>
                        <div class="info-value">
                            <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">
                            <i class="fas fa-clock me-2"></i>Appointment Time
                        </div>
                        <div class="info-value">
                            <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">
                            <i class="fas fa-calendar-plus me-2"></i>Booked On
                        </div>
                        <div class="info-value">
                            <?php echo date('M j, Y g:i A', strtotime($appointment['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <div class="action-buttons">
                        <a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>" class="btn btn-primary">
                            <i class="fas fa-envelope me-1"></i>Send Email
                        </a>
                        <a href="tel:<?php echo htmlspecialchars($appointment['phone']); ?>" class="btn btn-success">
                            <i class="fas fa-phone me-1"></i>Call Client
                        </a>
                        <button class="btn btn-outline-danger" onclick="deleteAppointment(<?php echo $appointment['id']; ?>)">
                            <i class="fas fa-trash me-1"></i>Delete Appointment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this appointment?</p>
                    <p><strong>Client:</strong> <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time'])); ?></p>
                    <p class="text-danger"><small><i class="fas fa-exclamation-triangle me-1"></i>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="admin-panel.php" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Delete Appointment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteAppointment(id) {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html> 