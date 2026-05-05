<?php
require_once '../connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank - VitalDrop</title>
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
                    <li class="nav-item"><a class="nav-link active" href="bloodbank.php">Blood Bank</a></li>
                    <li class="nav-item"><a class="nav-link" href="feedback.php">Feedback</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="../auth/register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1>Blood Bank Inventory</h1>
            <p>Real-time Blood Type Availability</p>
        </div>
    </div>

    <div class="container">
        <p class="intro">
View the current available blood types in our system. All blood types play a vital role in emergency situations and planned medical procedures.
        </p>

        <!-- Image Gallery Section -->
        <div class="gallery-section mb-5">
            <h3 class="text-center mb-4">Blood Donation & Transfusion</h3>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/image 3.jpg" alt="Blood Donation" class="gallery-img">
                        <p class="text-center mt-2"><strong>Blood Donation</strong></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/download.jpg" alt="Blood Transfusion" class="gallery-img">
                        <p class="text-center mt-2"><strong>Blood Transfusion</strong></p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="gallery-card">
                        <img src="../assets/images/download (1).jpg" alt="Blood Inventory" class="gallery-img">
                        <p class="text-center mt-2"><strong>Blood Bank Inventory</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blood Type Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card card-o">
                    <div class="blood-type">O+</div>
                    <p class="label">Universal Donor</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-o">
                    <div class="blood-type">O-</div>
                    <p class="label">Universal Donor</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-a">
                    <div class="blood-type">A+</div>
                    <p class="label">Common</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-a">
                    <div class="blood-type">A-</div>
                    <p class="label">Rare</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-b">
                    <div class="blood-type">B+</div>
                    <p class="label">Common</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-b">
                    <div class="blood-type">B-</div>
                    <p class="label">Rare</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-ab">
                    <div class="blood-type">AB+</div>
                    <p class="label">Universal Recipient</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-ab">
                    <div class="blood-type">AB-</div>
                    <p class="label">Rarest</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="btn-back">
            <a href="home.html">← Back to Home</a>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2026 VitalDrop Blood Donation System. Every Drop Counts.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
