<?php
session_start();

// Redirect if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form submission
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // For simplicity, hardcoded credentials (in production, these should be in a DB with hashed passwords)
    $admin_username = 'admin';
    $admin_password = 'admin123';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hypnotherapy</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-shield fa-3x mb-3"></i>
                <h3>Admin Login</h3>
                <p class="mb-0 opacity-75">Welcome back! Please sign in to your account.</p>
            </div>
            <div class="login-body">
                <?php if($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" novalidate>
                    <div class="mb-4">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                               required>
                        <div class="invalid-feedback">
                            Please enter your username.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <div class="position-relative">
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required>
                            <button type="button" 
                                    class="btn btn-link position-absolute end-0 top-0 me-2 mt-2 p-0" 
                                    id="togglePassword"
                                    style="border: none; background: none; color: var(--text-secondary);">
                                <i class="fas fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </div>
                </form>
                
                <div class="text-center">
                    <hr class="my-4">
                    <a href="../index.html" class="btn btn-link">
                        <i class="fas fa-arrow-left me-2"></i>Return to Website
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery-3.7.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
        
        // Form validation
        (function() {
            'use strict';
            
            const form = document.querySelector('form');
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
            
            // Focus on username field when page loads
            document.getElementById('username').focus();
        })();
        
        // Add loading state on form submission
        document.querySelector('form').addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html> 