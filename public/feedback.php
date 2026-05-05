<?php
require_once '../connect.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $rating = sanitize_input($_POST['rating'] ?? '');
    $feedback_text = sanitize_input($_POST['feedback'] ?? '');
    
    if (empty($name) || empty($email) || empty($rating) || empty($feedback_text)) {
        $message = 'All fields are required!';
        $message_type = 'error';
    } else {
        $insert_sql = "INSERT INTO feedback (name, email, rating, message, subject, created_at) 
                      VALUES ('" . escape_db_input($name) . "', '" . escape_db_input($email) . "', 
                              '" . escape_db_input($rating) . "', '" . escape_db_input($feedback_text) . "', 'User Feedback', NOW())";
        
        if ($conn->query($insert_sql)) {
            $message = 'Thank you for your feedback! Your thoughts help us improve.';
            $message_type = 'success';
            $_POST = array();
        } else {
            $message = 'Failed to submit feedback. Please try again.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - VitalDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/global-styles.css">
    
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="home.html"><i class="bi bi-droplet-fill"></i> VitalDrop</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="home.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="aboutus.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="bloodbank.php">Blood Bank</a></li>
                    <li class="nav-item"><a class="nav-link active" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1>Share Your Feedback</h1>
            <p>Your opinions help us create a better experience for everyone</p>
        </div>
    </div>

    <!-- Image Gallery Section -->
    <div class="container mt-5 mb-5">
        <div class="gallery-section mb-5">
            <h3 class="text-center mb-4">Our Work in Action</h3>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/image2.jpg" alt="Community Help" class="gallery-img">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/download (2).jpg" alt="Donation Process" class="gallery-img">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/I'm A Proud Blood Donor Sticker.jpg" alt="Blood Donor" class="gallery-img">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/Blood transfusion bag object – Royalty-Free Vector _ VectorStock.jpg" alt="Blood Transfusion" class="gallery-img">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-container">
        <div class="feedback-form">
            <?php if (!empty($message)): ?>
                <div class="alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?>">
                    <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Your full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">How would you rate your experience?</label>
                    <select class="form-select" name="rating" required>
                        <option value="">Select your rating</option>
                        <option value="5">Excellent (5)</option>
                        <option value="4">Good (4)</option>
                        <option value="3">Average (3)</option>
                        <option value="2">Poor (2)</option>
                        <option value="1">Very Poor (1)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Your Feedback</label>
                    <textarea class="form-control" name="feedback" rows="6" placeholder="Tell us what you think.." required></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    Submit Feedback
                </button>
            </form>
        </div>

        <div class="back-button-container">
            <a href="home.html" class="btn btn-outline-secondary btn-sm">
                ← Back to Home
            </a>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 VitalDrop Blood Donation System. We Value Your Input.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
