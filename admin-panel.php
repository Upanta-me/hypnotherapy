<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin-login.php");
    exit();
}

// DB connection
$conn = new mysqli("localhost", "root", "", "appointment");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle appointment deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $delete_sql = "DELETE FROM appointments WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $appointment_id);
    
    if ($stmt->execute()) {
        $message = "Appointment deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting appointment.";
        $message_type = "error";
    }
    $stmt->close();
}

// Get search and filter parameters
$search = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
$date_filter = isset($_GET['date_filter']) ? htmlspecialchars(trim($_GET['date_filter'])) : '';
$service_filter = isset($_GET['service_filter']) ? htmlspecialchars(trim($_GET['service_filter'])) : '';

// Build query with filters
$sql = "SELECT * FROM appointments WHERE 1=1";
$params = array();
$types = "";

if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if (!empty($date_filter)) {
    $sql .= " AND appointment_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if (!empty($service_filter)) {
    $sql .= " AND service = ?";
    $params[] = $service_filter;
    $types .= "s";
}

$sql .= " ORDER BY appointment_date DESC, appointment_time DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total_appointments,
    COUNT(CASE WHEN appointment_date >= CURDATE() THEN 1 END) as upcoming_appointments,
    COUNT(CASE WHEN appointment_date < CURDATE() THEN 1 END) as past_appointments,
    COUNT(CASE WHEN appointment_date = CURDATE() THEN 1 END) as today_appointments
    FROM appointments";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hypnotherapy and Cosmic Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .admin-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; padding: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: white; border-radius: 10px; padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #667eea;
        }
        .stats-card h3 { color: #667eea; font-size: 2rem; }
        .appointments-table {
            background: white; border-radius: 10px; overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table th { background-color: #667eea; color: white; border: none; }
        .filter-section {
            background: white; padding: 1.5rem; border-radius: 10px;
            margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><i class="fas fa-user-shield me-2"></i>Admin Dashboard</h2>
                    <p class="mb-0">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="index.html" class="btn btn-light me-2"><i class="fas fa-home me-1"></i>Website</a>
                    <a href="admin-logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['total_appointments']; ?></h3>
                    <p class="mb-0 text-muted">Total Appointments</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['upcoming_appointments']; ?></h3>
                    <p class="mb-0 text-muted">Upcoming</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['today_appointments']; ?></h3>
                    <p class="mb-0 text-muted">Today</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <h3><?php echo $stats['past_appointments']; ?></h3>
                    <p class="mb-0 text-muted">Past</p>
                </div>
            </div>
        </div>

        <div class="filter-section">
            <h5><i class="fas fa-filter me-2"></i>Filter Appointments</h5>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="date_filter" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="service_filter">
                        <option value="">All Services</option>
                        <option value="consultancy" <?php echo $service_filter === 'consultancy' ? 'selected' : ''; ?>>Consultancy</option>
                        <option value="per_session_hypnotherapy" <?php echo $service_filter === 'per_session_hypnotherapy' ? 'selected' : ''; ?>>Hypnotherapy</option>
                        <option value="past_life_regression" <?php echo $service_filter === 'past_life_regression' ? 'selected' : ''; ?>>Past Life Regression</option>
                        <option value="inner_child_healing" <?php echo $service_filter === 'inner_child_healing' ? 'selected' : ''; ?>>Inner Child Healing</option>
                        <option value="womb_healing" <?php echo $service_filter === 'womb_healing' ? 'selected' : ''; ?>>Womb Healing</option>
                        <option value="age_regression" <?php echo $service_filter === 'age_regression' ? 'selected' : ''; ?>>Age Regression</option>
                        <option value="theta_healing" <?php echo $service_filter === 'theta_healing' ? 'selected' : ''; ?>>Theta Healing</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>

        <div class="appointments-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr><td colspan="8" class="text-center py-4">No appointments found</td></tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo $appointment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                                    <td>
                                        <?php 
                                        $services = [
                                            'consultancy' => 'Consultancy',
                                            'per_session_hypnotherapy' => 'Hypnotherapy',
                                            'past_life_regression' => 'Past Life Regression',
                                            'inner_child_healing' => 'Inner Child Healing',
                                            'womb_healing' => 'Womb Healing',
                                            'age_regression' => 'Age Regression',
                                            'theta_healing' => 'Theta Healing'
                                        ];
                                        echo $services[$appointment['service']] ?? $appointment['service'];
                                        ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></td>
                                    <td><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>" class="btn btn-sm btn-outline-info" title="Send Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            <a href="tel:<?php echo htmlspecialchars($appointment['phone']); ?>" class="btn btn-sm btn-outline-success" title="Call Client">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this appointment?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Appointment">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 