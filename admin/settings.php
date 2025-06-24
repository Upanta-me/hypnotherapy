<?php
require_once 'includes/header.php';

// Connect to database
$conn = new mysqli("localhost", "root", "", "appointment");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create settings table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS admin_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!$conn->query($create_table_sql)) {
    die("Error creating table: " . $conn->error);
}

// Default settings
$default_settings = [
    ['company_name', 'Hypnotherapy and COSMIC HUB', 'Company name displayed throughout the website'],
    ['company_phone', '+(91) 9829422484', 'Primary contact phone number'],
    ['company_email', 'contact@example.com', 'Primary contact email address'],
    ['company_address', 'Chandiram Bora Path, Ketekibari, Tezpur, Da-Parbatia Gaon, Assam 784001', 'Physical address'],
    ['business_hours', 'Monday-Friday: 9AM-5PM', 'Business hours'],
    ['appointment_buffer', '15', 'Buffer time between appointments (minutes)'],
    ['max_daily_appointments', '10', 'Maximum number of appointments per day'],
    ['max_appointments_per_client', '2', 'Maximum appointments allowed per client']
];

// Insert default settings if they don't exist
$stmt = $conn->prepare("INSERT IGNORE INTO admin_settings (setting_key, setting_value, setting_description) VALUES (?, ?, ?)");
foreach ($default_settings as $setting) {
    $stmt->bind_param("sss", $setting[0], $setting[1], $setting[2]);
    $stmt->execute();
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'action') {
            $clean_value = htmlspecialchars(trim($value));
            $stmt = $conn->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param("ss", $clean_value, $key);
            $stmt->execute();
        }
    }
    
    $_SESSION['flash_message'] = "Settings updated successfully!";
    $_SESSION['flash_message_type'] = "success";
    
    header("Location: settings.php");
    exit;
}

// Fetch all settings
$result = $conn->query("SELECT * FROM admin_settings ORDER BY setting_key");
$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row;
}

$conn->close();
?>

<div class="container-fluid py-4">
    <h1 class="h2 mb-4">Settings</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">System Settings</h6>
        </div>
        <div class="card-body">
            <form method="post" action="settings.php">
                <input type="hidden" name="action" value="update_settings">
                
                <h5 class="mb-3">Company Information</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                value="<?php echo htmlspecialchars($settings['company_name']['setting_value'] ?? ''); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['company_name']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="company_email" name="company_email" 
                                value="<?php echo htmlspecialchars($settings['company_email']['setting_value'] ?? ''); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['company_email']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="company_phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone" 
                                value="<?php echo htmlspecialchars($settings['company_phone']['setting_value'] ?? ''); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['company_phone']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="business_hours" class="form-label">Business Hours</label>
                            <input type="text" class="form-control" id="business_hours" name="business_hours" 
                                value="<?php echo htmlspecialchars($settings['business_hours']['setting_value'] ?? ''); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['business_hours']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="company_address" class="form-label">Address</label>
                            <textarea class="form-control" id="company_address" name="company_address" rows="2"><?php echo htmlspecialchars($settings['company_address']['setting_value'] ?? ''); ?></textarea>
                            <div class="form-text text-muted">
                                <?php echo $settings['company_address']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <h5 class="mb-3">Appointment Settings</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="appointment_buffer" class="form-label">Appointment Buffer (minutes)</label>
                            <input type="number" class="form-control" id="appointment_buffer" name="appointment_buffer" min="0" 
                                value="<?php echo htmlspecialchars($settings['appointment_buffer']['setting_value'] ?? '15'); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['appointment_buffer']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_daily_appointments" class="form-label">Max Daily Appointments</label>
                            <input type="number" class="form-control" id="max_daily_appointments" name="max_daily_appointments" min="1" 
                                value="<?php echo htmlspecialchars($settings['max_daily_appointments']['setting_value'] ?? '10'); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['max_daily_appointments']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_appointments_per_client" class="form-label">Max Appointments Per Client</label>
                            <input type="number" class="form-control" id="max_appointments_per_client" name="max_appointments_per_client" min="1" 
                                value="<?php echo htmlspecialchars($settings['max_appointments_per_client']['setting_value'] ?? '2'); ?>">
                            <div class="form-text text-muted">
                                <?php echo $settings['max_appointments_per_client']['setting_description'] ?? ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Admin Account</h6>
        </div>
        <div class="card-body">
            <form method="post" action="change_password.php" class="row g-3">
                <div class="col-md-4">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="col-md-4">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="col-md-4">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 