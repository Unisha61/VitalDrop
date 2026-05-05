<?php
include 'includes/header_admin.php';

// Get statistics
$donor_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='donor'")->fetch_assoc()['count'];
$recipient_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='recipient'")->fetch_assoc()['count'];
$appointment_count = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
$pending_appt = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE status='pending'")->fetch_assoc()['count'];
$total_ml = $conn->query("SELECT COALESCE(SUM(quantity), 0) FROM blood_inventory")->fetch_assoc()['COALESCE(SUM(quantity), 0)'];
?>



<div class="container">
    <div class="dashboard-header">
        <div class="container">
            <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <p>System Overview & Control Center</p>
        </div>
    </div>

    <h3>Key Statistics</h3>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div class="stat-label">Donors</div>
            <div class="stat-value"><?php echo $donor_count; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-hospital"></i></div>
            <div class="stat-label">Recipients</div>
            <div class="stat-value"><?php echo $recipient_count; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div class="stat-label">Total Appointments</div>
            <div class="stat-value"><?php echo $appointment_count; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-clock"></i></div>
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?php echo $pending_appt; ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="bi bi-droplet"></i></div>
            <div class="stat-label">Blood in Stock (ml)</div>
            <div class="stat-value"><?php echo $total_ml; ?></div>
        </div>
    </div>

    <h3 class="mt-4 mb-3">Quick Access</h3>
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <a href="donor_manage.php" class="action-btn">
                <i class="bi bi-people"></i>
                <div>Donors</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="recipient_manage.php" class="action-btn">
                <i class="bi bi-hospital"></i>
                <div>Recipients</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="donations_manage.php" class="action-btn">
                <i class="bi bi-droplet"></i>
                <div>Donations</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="appointment_manage.php" class="action-btn">
                <i class="bi bi-calendar-check"></i>
                <div>Appointments</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="blood_inventory.php" class="action-btn">
                <i class="bi bi-box-seam"></i>
                <div>Inventory</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="request_manage.php" class="action-btn">
                <i class="bi bi-clipboard-pulse"></i>
                <div>Blood Requests</div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="feedback_manage.php" class="action-btn">
                <i class="bi bi-chat-left-text"></i>
                <div>Feedback</div>
            </a>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer_admin.php'; ?>