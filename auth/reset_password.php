<?php
session_start();
require_once '../connect.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$valid_token = false;
$email = '';

if (empty($token)) {
    $error = "Invalid or missing token.";
} else {
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $valid_token = true;
        $row = $result->fetch_assoc();
        $email = $row['email'];
    } else {
        $error = "This password reset link is invalid or has expired.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE email = ?");
        $update_stmt->bind_param("ss", $hashed_password, $email);
        
        if ($update_stmt->execute()) {
            $success = "Your password has been successfully reset. You can now log in.";
            $valid_token = false; // Hide the form
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - VitalDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/css/global-styles.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><i class="bi bi-droplet-fill"></i> VitalDrop</h1>
                <p class="auth-subtitle">Set new password</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <i class="bi bi-check-circle-fill"></i> <?php echo htmlspecialchars($success); ?>
                </div>
                <a href="login.php" class="auth-submit-btn" style="display: block; text-align: center; text-decoration: none;">
                    <i class="bi bi-box-arrow-in-right"></i> Go to Login
                </a>
            <?php elseif ($valid_token): ?>
                <form method="POST">
                    <div class="auth-form-group">
                        <label class="auth-label"><i class="bi bi-lock"></i> New Password</label>
                        <input type="password" class="auth-input" name="password" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="auth-form-group">
                        <label class="auth-label"><i class="bi bi-lock-fill"></i> Confirm Password</label>
                        <input type="password" class="auth-input" name="confirm_password" placeholder="Repeat password" required>
                    </div>
                    <button type="submit" class="auth-submit-btn">
                        <i class="bi bi-check2-circle"></i> Reset Password
                    </button>
                </form>
            <?php else: ?>
                <div class="auth-link-container">
                    <a href="forgot_password.php"><i class="bi bi-arrow-left"></i> Request New Link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
