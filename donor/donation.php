<?php
include 'includes/header_donor.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];

// Get detailed donation statistics
$donations = $conn->query("SELECT * FROM donations WHERE donor_id=$user_id ORDER BY donation_date DESC");
$total_donations = $donations->num_rows;

// Calculate statistics
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(units) as total_units,
        AVG(units) as avg_units,
        MAX(donation_date) as last_date,
        MIN(donation_date) as first_date
    FROM donations 
    WHERE donor_id=$user_id AND status='completed'
")->fetch_assoc();

$stats_by_status = $conn->query("
    SELECT status, COUNT(*) as count 
    FROM donations 
    WHERE donor_id=$user_id 
    GROUP BY status
");

$status_breakdown = [];
while ($row = $stats_by_status->fetch_assoc()) {
    $status_breakdown[$row['status']] = $row['count'];
}

// Calculate impact
$lives_saved = ($stats['total_units'] ?? 0) * 3;
$avg_donation_time = $stats['total'] > 0 ? (strtotime($stats['last_date']) - strtotime($stats['first_date'])) / ($stats['total'] - 1) : 0;
$avg_donation_days = $avg_donation_time > 0 ? round($avg_donation_time / 86400) : 0;
?>



<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-droplet-fill"></i></div>
        <div class="stat-label">Total Donations</div>
        <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-box"></i></div>
        <div class="stat-label">Total Volume (ml)</div>
        <div class="stat-value"><?php echo $stats['total_units'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-heart-fill"></i></div>
        <div class="stat-label">Estimated Impact</div>
        <div class="stat-value text-danger"><?php echo $lives_saved; ?></div>
        <div class="small text-muted mt-1">Lives Saved</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
        <div class="stat-label">Avg Interval</div>
        <div class="stat-value"><?php echo $avg_donation_days; ?></div>
        <div class="small text-muted mt-1">Days Between</div>
    </div>
</div>

<!-- Status Breakdown -->
<?php if (count($status_breakdown) > 0): ?>
<div class="achievements-header">
    <h6>Donation Status Breakdown</h6>
    <div class="row">
        <?php foreach ($status_breakdown as $status => $count): ?>
        <div class="col-md-3">
            <div class="text-center">
                <span class="badge bg-<?php echo match($status) {
                    'completed' => 'success',
                    'pending' => 'warning',
                    'cancelled' => 'danger',
                    default => 'secondary'
                }; ?> achievement-badge">
                    <?php echo $count; ?> <?php echo ucfirst($status); ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Donation History</h5>
    </div>
    <div class="table-container mb-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Blood Type</th>
                    <th>Volume</th>
                    <th>Status</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($total_donations === 0) {
                    echo '<tr><td colspan="5" class="text-center text-muted py-4">No donation records yet</td></tr>';
                } else {
                    while($row = $donations->fetch_assoc()): 
                ?>
                <tr>
                    <td class="fw-bold"><?php echo date('M d, Y', strtotime($row['donation_date'])); ?></td>
                    <td><span class="badge bg-danger"><?php echo htmlspecialchars($row['blood_type']); ?></span></td>
                    <td><?php echo $row['units']; ?> ml</td>
                    <td>
                        <span class="badge bg-<?php echo match($row['status']) {
                            'completed' => 'success',
                            'pending' => 'warning',
                            'rejected' => 'danger',
                            default => 'secondary'
                        }; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?php echo htmlspecialchars($row['notes'] ?? '—'); ?></td>
                </tr>
                <?php endwhile; } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Achievements -->
<?php if ($stats['total'] > 0): ?>
<div class="achievements-header">
    <h6>🏆 Your Achievements</h6>
    <div class="row">
        <?php 
        $achievements = [];
        if ($stats['total'] >= 1) $achievements[] = '⭐ First Donor';
        if ($stats['total'] >= 5) $achievements[] = '⭐ 5 Donations';
        if ($stats['total'] >= 10) $achievements[] = '⭐ Regular Donor';
        if ($stats['total'] >= 25) $achievements[] = '🌟 Gold Donor';
        if ($stats['total'] >= 50) $achievements[] = '🏆 Platinum Donor';
        if ($lives_saved >= 50) $achievements[] = '❤️ Life Saver';
        
        foreach ($achievements as $achievement):
        ?>
        <div class="col-md-4 mb-2">
            <div class="alert alert-info mb-0" style="padding: 8px 12px; font-size: 0.9rem;">
                <?php echo $achievement; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="back-btn">
    <a href="donordashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include 'includes/footer_donor.php'; ?>
