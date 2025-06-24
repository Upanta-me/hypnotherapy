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
$conn->close();
?>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h2 mb-0">Appointment Details</h1>
        <div>
            <a href="appointments.php" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Appointments
            </a>
            <a href="#" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Appointment #<?php echo $appointment['id']; ?></h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold">Name</h5>
                            <p><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold">Email</h5>
                            <p><?php echo htmlspecialchars($appointment['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold">Phone</h5>
                            <p><?php echo htmlspecialchars($appointment['phone']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold">Service</h5>
                            <p><?php echo htmlspecialchars($appointment['service']); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold">Date</h5>
                            <p><?php echo date('F j, Y', strtotime($appointment['appointment_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5 class="small font-weight-bold">Time</h5>
                            <p><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <h5 class="small font-weight-bold">Booked On</h5>
                            <p><?php echo date('F j, Y \a\t g:i A', strtotime($appointment['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Calendar Widget -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Appointment Date</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="calendar-date mb-3">
                            <div class="calendar-month"><?php echo date('M', strtotime($appointment['appointment_date'])); ?></div>
                            <div class="calendar-day"><?php echo date('d', strtotime($appointment['appointment_date'])); ?></div>
                            <div class="calendar-year"><?php echo date('Y', strtotime($appointment['appointment_date'])); ?></div>
                        </div>
                        <div class="calendar-time">
                            <i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Info Quick Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Contact</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h1 mb-0">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4 class="mt-2"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></h4>
                    </div>
                    
                    <div class="mb-2">
                        <a href="mailto:<?php echo htmlspecialchars($appointment['email']); ?>" class="btn btn-outline-primary btn-sm btn-block">
                            <i class="fas fa-envelope"></i> Send Email
                        </a>
                    </div>
                    
                    <div>
                        <a href="tel:<?php echo htmlspecialchars($appointment['phone']); ?>" class="btn btn-outline-success btn-sm btn-block">
                            <i class="fas fa-phone"></i> Call
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
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

<style>
    .calendar-date {
        border: 1px solid #e3e6f0;
        border-radius: 5px;
        padding: 10px;
        max-width: 150px;
        margin: 0 auto;
    }
    
    .calendar-month {
        background-color: var(--secondary-color);
        color: white;
        padding: 5px 0;
        font-weight: bold;
        border-radius: 3px 3px 0 0;
    }
    
    .calendar-day {
        font-size: 2.5rem;
        font-weight: bold;
        padding: 10px 0;
    }
    
    .calendar-year {
        border-top: 1px solid #e3e6f0;
        padding: 5px 0;
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .calendar-time {
        font-size: 1.2rem;
        color: #6c757d;
    }
</style>

<?php require_once 'includes/footer.php'; ?> 