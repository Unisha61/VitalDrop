<?php
require_once '../connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - VitalDrop</title>
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
                    <li class="nav-item"><a class="nav-link active" href="aboutus.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="bloodbank.php">Blood Bank</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1>About VitalDrop</h1>
            <p>Saving Lives Through Blood Donation</p>
        </div>
    </div>

    <div class="container">
        <!-- Mission Section -->
        <div class="content-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=500&h=350&fit=crop" alt="Our Mission" class="about-img">
                </div>
                <div class="col-md-6">
                    <h2 class="section-title">Our Mission</h2>
                    <p class="section-content">
VitalDrop is dedicated to connecting blood donors with recipients in need. Our mission is to create a seamless, secure, and efficient platform that saves lives through blood donation and distribution. We believe that every drop of blood has the power to change lives, and we're committed to making the donation process easier for everyone.
                    </p>
                </div>
            </div>
        </div>
<!-- What We Do Section -->
<div class="content-section">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h2 class="section-title">
                What We Do
            </h2>
            <ul class="feature-list">
                <li><strong>Blood Matching:</strong> We connect donors and patients based on compatible blood types.</li>
                <li><strong>Easy Appointments:</strong> Book and manage donation appointments easily.</li>
                <li><strong>Live Stock Updates:</strong> Check available blood types in real time.</li>
                <li><strong>Secure System:</strong> Your data is safe and protected.</li>
                <li><strong>24/7 Help:</strong> Support is always available when you need it.</li>
            </ul>
        </div>
        <div class="col-md-6">
            <img src="../assets/images/image1.jpg" alt="What We Do" class="about-img">
        </div>
    </div>
</div>

        <!-- Impact Section -->
        <div class="content-section">
            <h2 class="section-title">Why Blood Donation Matters</h2>
            <p class="section-content">
Blood donation is one of the most critical healthcare services. Every single donation can save up to three lives. Whether it's for surgery, accident victims, or patients with chronic conditions, blood transfusions are essential medical interventions. By joining VitalDrop, you're not just registering – you're becoming a hero who can save lives at a moment's notice.
            </p>
        </div>

        <!-- Why Choose Us Section -->
        <div class="content-section">
            <h2 class="section-title">Why Choose VitalDrop?</h2>
            <div class="row mt-4">
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h5>Fast & Easy</h5>
                        <p>Simple registration and quick donor-recipient matching</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h5>Secure & Private</h5>
                        <p>Your data is encrypted and protected at all times</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon">👥</div>
                        <h5>Community Driven</h5>
                        <p>Join thousands of donors and recipients saving lives</p>
                    </div>
                </div>
            </div>
        </div>

<!-- CTA Section -->
<div class="cta-section">
    <h3>Want to Help Save Lives?</h3>
    <p>Join us today and make a difference.</p>
    
    <a href="../auth/register.php" class="cta-btn">
        Register
    </a>
    
    <a href="home.html" class="cta-btn" style="background: transparent; color: white; border: 2px solid white;">
        Home
    </a>
</div>

    </div>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 VitalDrop Blood Donation System. Saving Lives, One Drop at a Time.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
