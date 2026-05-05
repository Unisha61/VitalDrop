<?php
session_start();
require_once '../connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $token, $expiry, $email);
            $update_stmt->execute();
            
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            $success = "Password reset link generated. <br><br><strong><a href='$reset_link'>Click here to reset password</a></strong><br><br><small>(Note: Simulated email for project demonstration)</small>";
        } else {
            $error = "No account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - VitalDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/css/global-styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-droplet-fill"></i> VitalDrop</h1>
                <p class="auth-subtitle">Reset your password</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="bi bi-check-circle-fill"></i> Reset link sent! Check your email or use the link below.
                </div>
                <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <a href="<?php echo $reset_link; ?>" class="btn btn-primary" style="width: 100%; background: var(--red-primary); border: none;">
                        <i class="bi bi-link-45deg"></i> Click Here to Reset Password
                    </a>
                    <small style="display: block; margin-top: 10px; color: #666;">
                        (Note: Simulated email for project demonstration)
                    </small>
                </div>
            <?php else: ?>
                <p style="color: var(--text-light); font-size: 0.95rem; margin-bottom: 20px; text-align: center;">
                    Enter your email address to receive a password reset link.
                </p>
                <form method="POST">
                    <div class="auth-form-group">
                        <label class="auth-label"><i class="bi bi-envelope"></i> Email Address</label>
                        <input type="email" class="auth-input" name="email" placeholder="your@email.com" required>
                    </div>
                    <button type="submit" class="auth-submit-btn">
                        <i class="bi bi-send"></i> Send Reset Link
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="auth-link-container">
                <a href="login.php"><i class="bi bi-arrow-left"></i> Back to Login</a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
