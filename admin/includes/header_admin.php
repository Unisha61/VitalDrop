<?php
require_once __DIR__ . '/../../connect.php';
require_role('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - VitalDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/css/global-styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="adminpages.php">
                <i class="bi bi-droplet-fill"></i> VitalDrop Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="adminpages.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donor_manage.php"> Donors</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="recipient_manage.php">Recipients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donations_manage.php">Appointments & Donations Manage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="request_manage.php">Blood Request Manage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blood_inventory.php">Blood Inventory</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback_manage.php">Feedback</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-4">
