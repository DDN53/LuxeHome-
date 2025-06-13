<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $pass = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($user = $res->fetch_assoc()) {
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                
              
                if ($remember) {
                    $session_duration = 60 * 60 * 24 * 1; 
                    session_set_cookie_params($session_duration);
                }
                
                session_regenerate_id(true);
                
                header('Location: product.php');
                exit;
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}
if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
    <div class="success-message">Registered successfully! Please log in.</div>
<?php endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Modern App</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <div class="container">
        <div class="left-panel">
            <i class="fas fa-lock icon"></i>
            <h2>Welcome Back</h2>
            <p>Sign in to access </p>
        </div>
        
        <div class="right-panel">
            <div class="logo">
                <h1>Luxe Home</h1>
                <p>Secure login to your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
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
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-password">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginButton">
                    <span id="buttonText">Login</span>
                </button>
                <div class="error-message" style="display: <?php echo $error ? 'block' : 'none'; ?>;">
    <?php echo $error; ?>
</div>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                </div>
            </form>
            
          
            
            <div class="signup-link">
                Don't have an account? <a href="register.php">Sign up now</a>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
            
            // Form submission handling
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const loading = document.getElementById('loading');
            
            loginForm.addEventListener('submit', function() {
                // Show loading state
                buttonText.textContent = 'Authenticating...';
                loginButton.disabled = true;
                loading.style.display = 'block';
            });
            
            // Simple client-side validation
            const emailInput = document.getElementById('email');
            emailInput.addEventListener('blur', function() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.value)) {
                    this.style.borderColor = '#c62828';
                } else {
                    this.style.borderColor = '#ddd';
                }
            });
            
            passwordInput.addEventListener('blur', function() {
                if (this.value.length < 6) {
                    this.style.borderColor = '#c62828';
                } else {
                    this.style.borderColor = '#ddd';
                }
            });
        });
    </script>
</body>
</html>