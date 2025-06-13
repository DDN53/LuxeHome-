<?php
include('includes/db.php');
session_start();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission";
    } else {
        $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate password match
        if ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $error = "Email already registered";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users(name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    $_SESSION['registration_success'] = true;
                    header('Location: login.php');
                    exit();
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Luxe Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            display: flex;
            width: 100%;
            max-width: 900px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .left-panel h2 {
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .left-panel p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            max-width: 300px;
        }
        
        .left-panel .icon {
            font-size: 100px;
            margin-bottom: 30px;
            opacity: 0.8;
        }
        
        .right-panel {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #333;
            font-size: 28px;
            font-weight: 700;
        }
        
        .logo p {
            color: #666;
            font-size: 16px;
            margin-top: 8px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 14px 20px 14px 45px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .input-with-icon input:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
            outline: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
            cursor: pointer;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #eee;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            background: #e74c3c;
            transition: all 0.3s;
        }
        
        .password-hints {
            font-size: 13px;
            color: #777;
            margin-top: 8px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin: 20px 0;
        }
        
        .btn-register:hover {
            background: linear-gradient(135deg, #5a0db9 0%, #1c68f0 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .login-link a {
            color: #6a11cb;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .login-link a:hover {
            color: #2575fc;
            text-decoration: underline;
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: <?php echo $error ? 'block' : 'none'; ?>;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 10px 0;
        }
        
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #6a11cb;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .left-panel {
                padding: 40px 20px;
            }
            
            .right-panel {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <i class="fas fa-user-plus icon"></i>
            <h2>Join Us Today</h2>
            <p>Create your account to get started with our platform and access exclusive features.</p>
        </div>
        
        <div class="right-panel">
            <div class="logo">
                <h1>Luxe Home</h1>
                <p>Create your free account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" required placeholder="John Doe" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required placeholder="you@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required placeholder="Create a password" minlength="8">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                    <div class="password-hints">
                        <p>Use at least 8 characters with a mix of letters, numbers & symbols</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                        <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-register" id="registerButton">
                    <span id="buttonText">Create Account</span>
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                </div>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggles
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const strengthMeter = document.getElementById('strengthMeter');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Password strength meter
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Length check
                if (password.length >= 8) strength += 1;
                if (password.length >= 12) strength += 1;
                
                // Character variety checks
                if (/[A-Z]/.test(password)) strength += 1;
                if (/[0-9]/.test(password)) strength += 1;
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                
                // Update meter
                const width = (strength / 5) * 100;
                strengthMeter.style.width = width + '%';
                
                // Update color
                if (strength <= 1) {
                    strengthMeter.style.backgroundColor = '#e74c3c'; // Red
                } else if (strength <= 3) {
                    strengthMeter.style.backgroundColor = '#f39c12'; // Orange
                } else {
                    strengthMeter.style.backgroundColor = '#2ecc71'; // Green
                }
            });
            
            // Form submission handling
            const registerForm = document.getElementById('registerForm');
            const registerButton = document.getElementById('registerButton');
            const buttonText = document.getElementById('buttonText');
            const loading = document.getElementById('loading');
            
            registerForm.addEventListener('submit', function(e) {
                // Client-side validation
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Passwords do not match');
                    return;
                }
                
                if (password.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters long');
                    return;
                }
                
                // Show loading state
                buttonText.textContent = 'Creating Account...';
                registerButton.disabled = true;
                loading.style.display = 'block';
            });
        });
    </script>
</body>
</html>