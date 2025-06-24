<?php
require_once 'includes/header.php';

// Connect to database
$conn = new mysqli("localhost", "root", "", "appointment");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle appointment deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "Appointment deleted successfully!";
        $_SESSION['flash_message_type'] = "success";
    } else {
        $_SESSION['flash_message'] = "Error deleting appointment: " . $conn->error;
        $_SESSION['flash_message_type'] = "danger";
    }
    
    // Redirect to avoid form resubmission
    header("Location: appointments.php");
    exit;
}

// Pagination setup
$limit = 10; // records per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';

// Count total records for pagination
$total_records = 0;
if (!empty($search) || !empty($filter_date)) {
    $count_sql = "SELECT COUNT(*) as total FROM appointments WHERE 1=1";
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $count_sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
        $types .= "ssss";
    }
    
    if (!empty($filter_date)) {
        $count_sql .= " AND appointment_date = ?";
        $params[] = $filter_date;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_records = $row['total'];
    }
} else {
    $result = $conn->query("SELECT COUNT(*) as total FROM appointments");
    if ($row = $result->fetch_assoc()) {
        $total_records = $row['total'];
    }
}

$total_pages = ceil($total_records / $limit);

// Fetch appointments with filters
$sql = "SELECT * FROM appointments WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if (!empty($filter_date)) {
    $sql .= " AND appointment_date = ?";
    $params[] = $filter_date;
    $types .= "s";
}

$sql .= " ORDER BY appointment_date DESC, appointment_time ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result();

$conn->close();
?>

<div class="container-fluid py-4">
    <h1 class="h2 mb-4">Manage Appointments</h1>
    
    <!-- Search and Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Search & Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email or phone" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-5">
                    <input type="date" class="form-control" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Apply</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Appointments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold">All Appointments</h6>
            <span class="badge bg-primary"><?php echo $total_records; ?> Appointments</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
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
                        <?php if ($appointments->num_rows > 0): ?>
                            <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $appointment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['email']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['service']); ?></td>
                                    <td><?php echo $appointment['appointment_date']; ?></td>
                                    <td><?php echo $appointment['appointment_time']; ?></td>
                                    <td>
                                        <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $appointment['id']; ?>" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $appointment['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete the appointment for <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?> on <?php echo $appointment['appointment_date']; ?> at <?php echo $appointment['appointment_time']; ?>?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <a href="appointments.php?delete=<?php echo $appointment['id']; ?>" class="btn btn-danger">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">No appointments found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 