<?php
include 'includes/header_recipient.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Get transfusion history
$transfusions = $conn->query("
    SELECT * FROM transfusion_history 
    WHERE recipient_id=$user_id 
    ORDER BY transfusion_date DESC
");

// Calculate donation statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total_transfusions,
        SUM(units_received) as total_units,
        AVG(units_received) as avg_units
    FROM transfusion_history 
    WHERE recipient_id=$user_id
")->fetch_assoc();

// Get upcoming appointments
$upcoming = $conn->query("
    SELECT * FROM appointments 
    WHERE recipient_id=$user_id AND status IN ('pending', 'confirmed')
    AND appointment_date >= CURDATE()
    ORDER BY appointment_date ASC
");
?>



<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-label">Total Transfusions</div>
        <div class="stat-value"><?php echo $stats['total_transfusions'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-droplet-fill"></i></div>
        <div class="stat-label">Total ml Received</div>
        <div class="stat-value"><?php echo $stats['total_units'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-activity"></i></div>
        <div class="stat-label">Average Volume</div>
        <div class="stat-value"><?php echo round($stats['avg_units'] ?? 0, 1); ?></div>
        <div class="small text-muted mt-1">ml per session</div>
    </div>
</div>

<!-- Upcoming Transfusions -->
<?php if ($upcoming && $upcoming->num_rows > 0): ?>
<div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-calendar2-event"></i> Upcoming Transfusions</h6>
    </div>
    <div class="table-container mb-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($apt = $upcoming->fetch_assoc()): ?>
                <tr>
                    <td class="fw-bold"><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                    <td><?php echo $apt['appointment_time']; ?></td>
                    <td><span class="badge bg-warning"><?php echo ucfirst($apt['status']); ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Transfusion History -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-clock-history"></i> Transfusion History</h6>
    </div>
    <div class="table-container mb-0">
        <?php if ($transfusions && $transfusions->num_rows > 0): ?>
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Volume</th>
                        <th>Location</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trans = $transfusions->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold"><?php echo date('M d, Y', strtotime($trans['transfusion_date'])); ?></td>
                        <td><span class="badge bg-danger"><?php echo htmlspecialchars($trans['blood_type']); ?></span></td>
                        <td><?php echo $trans['units_received']; ?> ml</td>
                        <td class="small text-muted"><?php echo htmlspecialchars($trans['hospital_name'] ?? '—'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo match($trans['status'] ?? 'completed') {
                                'completed' => 'success',
                                'pending' => 'warning',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            }; ?>">
                                <?php echo ucfirst($trans['status'] ?? 'completed'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                No transfusion records found.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Medical Profile Summary -->
<?php if (!empty($user['medical_condition']) || !empty($user['medications'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-heart-pulse"></i> Medical Profile Summary</h6>
        <a href="edit_profile.php" class="btn btn-sm btn-outline-primary py-1 px-3">Edit Profile</a>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <?php if (!empty($user['medical_condition'])): ?>
            <div class="col-md-6">
                <div class="fw-bold small text-muted mb-1">Primary Condition</div>
                <div class="p-3 bg-light rounded text-dark small"><?php echo htmlspecialchars($user['medical_condition']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($user['medications'])): ?>
            <div class="col-md-6">
                <div class="fw-bold small text-muted mb-1">Current Medications</div>
                <div class="p-3 bg-light rounded text-dark small"><?php echo htmlspecialchars($user['medications']); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="mt-4">
    <a href="recipientdashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include 'includes/footer_recipient.php'; ?>
