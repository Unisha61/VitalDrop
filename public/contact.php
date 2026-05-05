<?php
require_once '../connect.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message_text = sanitize_input($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message_text)) {
        $message = 'All fields are required!';
        $message_type = 'error';
    } else {
        $insert_sql = "INSERT INTO contact (name, email, subject, message, created_at) 
                      VALUES ('" . escape_db_input($name) . "', '" . escape_db_input($email) . "', 
                              '" . escape_db_input($subject) . "', '" . escape_db_input($message_text) . "', NOW())";
        
        if ($conn->query($insert_sql)) {
            $message = 'Message sent successfully! We will get back to you soon.';
            $message_type = 'success';
            $_POST = array();
        } else {
            $message = 'Failed to send message. Please try again.';
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
    <title>Contact Us - VitalDrop</title>
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
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1>Get In Touch</h1>
            <p>We're here to help and answer any questions you might have</p>
        </div>
    </div>

    <!-- Image Gallery Section -->
    <div class="container mt-5 mb-5">
        <div class="gallery-section mb-5">
            <h3 class="text-center mb-4">Connecting Donors & Recipients</h3>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/image1.jpg" alt="Connection" class="gallery-img">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/download (3).jpg" alt="Support" class="gallery-img">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/Photo by Yunus Tuğ on Unsplash.jpg" alt="Healthcare" class="gallery-img">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/image2.jpg" alt="Community" class="gallery-img">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5 mb-5" style="max-width: 700px;">
        <div class="contact-form">
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
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" name="subject" placeholder="What is this about?" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea class="form-control" name="message" rows="6" placeholder="Your message here..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    Send Message
                </button>
            </form>
        </div>

        <div class="contact-info">
            <h3>Contact Information</h3>
            
            <div class="info-item">
                <span class="info-item-label">📞 Phone</span>
                <div class="info-item-value">+977 9800000000</div>
            </div>
            <div class="info-item">
                <span class="info-item-label">📧 Email</span>
                <div class="info-item-value">info@vitaldrop.com</div>
            </div>
            <div class="info-item">
                <span class="info-item-label">🕐 Support Hours</span>
                <div class="info-item-value">24/7 Emergency Support Available</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="home.html" class="btn btn-outline-secondary" style="padding: 12px 30px; font-weight: 600; border-radius: 6px;">
                ← Back to Home
            </a>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 VitalDrop Blood Donation System. We're Here to Help.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
