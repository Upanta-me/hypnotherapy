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
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Manage Appointments</h1>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-check me-2"></i>
                View and manage all appointment bookings
            </p>
        </div>
        <div>
            <a href="../book-appointment.html" class="btn btn-success" target="_blank">
                <i class="fas fa-plus me-2"></i>New Appointment
            </a>
        </div>
    </div>
    
    <!-- Search and Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-search me-2"></i>Search & Filter
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3" id="filterForm">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="search" 
                               placeholder="Search by name, email or phone" 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Filter by Date</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-calendar"></i>
                        </span>
                        <input type="date" 
                               class="form-control" 
                               name="filter_date" 
                               value="<?php echo htmlspecialchars($filter_date); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-filter me-2"></i>Apply Filter
                        </button>
                        <a href="appointments.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Appointments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list me-2"></i>All Appointments
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary"><?php echo number_format($total_records); ?> Total</span>
                <?php if ($search || $filter_date): ?>
                    <span class="badge bg-info">Filtered</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if ($appointments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Client</th>
                                <th>Contact</th>
                                <th>Service</th>
                                <th>Appointment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($appointment = $appointments->fetch_assoc()): ?>
                                <?php 
                                $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                $is_past = strtotime($appointment_datetime) < time();
                                $is_today = $appointment['appointment_date'] == date('Y-m-d');
                                $is_tomorrow = $appointment['appointment_date'] == date('Y-m-d', strtotime('+1 day'));
                                ?>
                                <tr>
                                    <td>
                                        <span class="text-muted">#<?php echo $appointment['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-3">
                                                <?php echo strtoupper(substr($appointment['first_name'], 0, 1) . substr($appointment['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">
                                                    <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                                                </div>
                                                <small class="text-muted">
                                                    Created <?php echo date('M j, Y', strtotime($appointment['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-envelope me-1 text-muted"></i>
                                            <small><?php echo htmlspecialchars($appointment['email']); ?></small>
                                        </div>
                                        <div>
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            <small><?php echo htmlspecialchars($appointment['phone']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <i class="fas fa-brain me-1"></i>
                                            <?php echo htmlspecialchars($appointment['service']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="font-weight-bold">
                                                <?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                            </small>
                                        </div>
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
                                        <?php elseif ($is_tomorrow): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-clock me-1"></i>Tomorrow
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-calendar me-1"></i>Scheduled
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="view-appointment.php?id=<?php echo $appointment['id']; ?>" 
                                               class="btn btn-sm btn-info" 
                                               data-bs-toggle="tooltip" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal<?php echo $appointment['id']; ?>" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Delete Modal -->
                                        <div class="modal fade" id="deleteModal<?php echo $appointment['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                                            <strong>Warning:</strong> This action cannot be undone.
                                                        </div>
                                                        <p>Are you sure you want to delete the appointment for:</p>
                                                        <div class="bg-light p-3 rounded">
                                                            <strong><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></strong><br>
                                                            <small class="text-muted">
                                                                <?php echo date('l, F j, Y', strtotime($appointment['appointment_date'])); ?> 
                                                                at <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                                                            </small>
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
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">No appointments found</h5>
                    <p class="text-muted mb-4">
                        <?php if ($search || $filter_date): ?>
                            No appointments match your search criteria. Try adjusting your filters.
                        <?php else: ?>
                            There are no appointment bookings yet. Appointments will appear here once they are created.
                        <?php endif; ?>
                    </p>
                    <?php if ($search || $filter_date): ?>
                        <a href="appointments.php" class="btn btn-primary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    <?php else: ?>
                        <a href="../book-appointment.html" class="btn btn-primary" target="_blank">
                            <i class="fas fa-plus me-2"></i>Create First Appointment
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            Showing <?php echo (($page - 1) * $limit) + 1; ?> to 
                            <?php echo min($page * $limit, $total_records); ?> of 
                            <?php echo number_format($total_records); ?> appointments
                        </small>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end_page < $total_pages): ?>
                                <?php if ($end_page < $total_pages - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>"><?php echo $total_pages; ?></a>
                                </li>
                            <?php endif; ?>
                            
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&filter_date=<?php echo urlencode($filter_date); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Additional JavaScript for enhanced functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit search form on Enter key
    const searchInput = document.querySelector('input[name="search"]');
    const dateInput = document.querySelector('input[name="filter_date"]');
    
    [searchInput, dateInput].forEach(input => {
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('filterForm').submit();
                }
            });
        }
    });
    
    // Confirm delete with enhanced modal
    document.querySelectorAll('[data-bs-target^="#deleteModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.querySelector(this.getAttribute('data-bs-target'));
            if (modal) {
                // Add shake animation to modal
                modal.addEventListener('shown.bs.modal', function() {
                    this.querySelector('.modal-content').style.animation = 'fadeInUp 0.3s ease-out';
                });
            }
        });
    });
    
    // Real-time search suggestions (debounced)
    let searchTimeout;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    // You can implement AJAX search suggestions here
                    console.log('Searching for:', query);
                }, 300);
            }
        });
    }
    
    // Enhanced tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { "show": 500, "hide": 100 }
        });
    });
});
</script>

<!-- Additional CSS for appointments page -->
<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 12px;
    flex-shrink: 0;
}

.table th {
    font-weight: 600;
    color: white;
    border: none;
    padding: 16px 12px;
}

.table td {
    border-color: var(--border-color);
    vertical-align: middle;
    padding: 16px 12px;
}

.btn-group .btn {
    border-radius: var(--border-radius);
    margin: 0 1px;
}

.pagination-sm .page-link {
    padding: 6px 12px;
    font-size: 13px;
}

.badge {
    font-size: 11px;
    padding: 4px 8px;
}

.input-group-text {
    background-color: var(--light-bg);
    border-color: var(--border-color);
    color: var(--text-secondary);
}

.form-label {
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 6px;
}

@media (max-width: 768px) {
    .table-responsive {
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin: 1px 0;
        border-radius: var(--border-radius) !important;
    }
    
    .avatar-circle {
        width: 30px;
        height: 30px;
        font-size: 10px;
    }
    
    .pagination {
        justify-content: center;
        margin-top: 1rem;
    }
}

.modal-content {
    border: none;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    border-bottom: 1px solid var(--border-color);
}

.modal-footer {
    border-top: 1px solid var(--border-color);
}

.alert-warning {
    border-left: 4px solid var(--warning-color);
}

/* Loading state for form submission */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
</style>

<?php require_once 'includes/footer.php'; ?> 