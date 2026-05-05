<?php
include 'includes/header_donor.php';
require_once '../filtering_matching.php';

// Get current user info (already loaded from header)
$user_id = $_SESSION['user_id'] ?? null;

// Refresh blood inventory dynamically
$inventory_sql = "SELECT * FROM blood_inventory ORDER BY blood_type ASC";
$inventory_result = $conn->query($inventory_sql);

$blood_inventory = [];
$total_units = 0;
if ($inventory_result && $inventory_result->num_rows > 0) {
    while($row = $inventory_result->fetch_assoc()) {
        $blood_inventory[] = $row;
        $total_units += $row['quantity'];
    }
}
?>

<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-droplet-fill text-primary"></i> Blood Inventory</h2>
        <p class="text-muted small mb-0">Current available blood units across all types.</p>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-box-seam"></i></div>
        <div class="stat-label">Total Volume</div>
        <div class="stat-value"><?php echo $total_units; ?> <small class="fs-6 text-muted">ml</small></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-label">In Stock Types</div>
        <div class="stat-value"><?php echo count(array_filter($blood_inventory, fn($b) => $b['quantity'] > 0)); ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-label">Out of Stock</div>
        <div class="stat-value"><?php echo count(array_filter($blood_inventory, fn($b) => $b['quantity'] <= 0)); ?></div>
    </div>
</div>

<div class="alert alert-primary py-2 px-3 small mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Note:</strong> 
    <?php if ($user && $user['role'] === 'recipient'): ?>
        Need blood? <a href="../recipient/blood_requests.php" class="fw-bold">Create a request</a>
    <?php elseif ($user && $user['role'] === 'donor'): ?>
        Help replenish stock! <a href="../donor/appointment.php" class="fw-bold">Schedule a donation</a>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="table-container mb-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Blood Type</th>
                    <th>Available Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($blood_inventory as $item): 
                    $is_available = $item['quantity'] > 0;
                ?>
                <tr>
                    <td class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($item['blood_type']); ?></td>
                    <td>
                        <div class="fw-bold <?php echo $is_available ? 'text-success' : 'text-danger'; ?>">
                            <?php echo $item['quantity']; ?> ml
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-<?php echo $is_available ? 'success' : 'danger'; ?> px-3">
                            <i class="bi bi-<?php echo $is_available ? 'check' : 'x'; ?>-circle me-1"></i>
                            <?php echo $is_available ? 'In Stock' : 'Out of Stock'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    </div>

<?php include 'includes/footer_donor.php'; ?>