<?php
include 'includes/header_recipient.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT blood_type FROM users WHERE id=$user_id")->fetch_assoc();
$recipient_blood_type = $user['blood_type'] ?? 'Unknown';

// Get blood inventory
$inventory = $conn->query("SELECT * FROM blood_inventory ORDER BY blood_type ASC");

// Blood type compatibility mapping
$compatible_donors = [
    'O+' => ['O+', 'O-'],
    'O-' => ['O-'],
    'A+' => ['A+', 'A-', 'O+', 'O-'],
    'A-' => ['A-', 'O-'],
    'B+' => ['B+', 'B-', 'O+', 'O-'],
    'B-' => ['B-', 'O-'],
    'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
    'AB-' => ['AB-', 'A-', 'B-', 'O-']
];

// Get recipient's compatible blood types
$compatible_types = $compatible_donors[$recipient_blood_type] ?? [];

// Get recipient's transfusion history
$transfusion_history = $conn->query("SELECT * FROM transfusion_history WHERE recipient_id=$user_id ORDER BY transfusion_date DESC LIMIT 10");

// Get recipient's stats
$stats = [];
$units_received_result = $conn->query("SELECT SUM(units_received) as total FROM transfusion_history WHERE recipient_id=$user_id AND status='completed'");
$stats['units_received'] = $units_received_result->fetch_assoc()['total'] ?? 0;

$requests_result = $conn->query("SELECT COUNT(*) as total FROM blood_requests WHERE recipient_id=$user_id");
$stats['total_requests'] = $requests_result->fetch_assoc()['total'] ?? 0;

$fulfilled_result = $conn->query("SELECT COUNT(*) as total FROM blood_requests WHERE recipient_id=$user_id AND status='fulfilled'");
$stats['fulfilled_requests'] = $fulfilled_result->fetch_assoc()['total'] ?? 0;

// Get all blood inventory for display
$blood_inventory = [];
$total_units = 0;
if ($inventory && $inventory->num_rows > 0) {
    while($row = $inventory->fetch_assoc()) {
        $blood_inventory[] = $row;
        $total_units += $row['quantity'];
    }
}
?>

    

    <div class="container-fluid" style="padding: 0 20px 20px 20px;">
<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-droplet-fill text-primary"></i> Blood Inventory</h2>
        <p class="text-muted small mb-0">Monitor availability and your transfusion history.</p>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-droplet-fill"></i></div>
        <div class="stat-label">Blood Received</div>
        <div class="stat-value"><?php echo $stats['units_received']; ?> <small class="fs-6">ml</small></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-file-earmark-text"></i></div>
        <div class="stat-label">Total Requests</div>
        <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-label">Fulfilled</div>
        <div class="stat-value"><?php echo $stats['fulfilled_requests']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-warning"><i class="bi bi-heart"></i></div>
        <div class="stat-label">Your Type</div>
        <div class="stat-value"><?php echo $recipient_blood_type; ?></div>
    </div>
</div>

<div class="alert alert-info py-2 px-3 small mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Compatible Donors:</strong> You can receive blood from types: <strong><?php echo implode(', ', $compatible_types); ?></strong>
</div>

        <!-- Information Alert -->
        <div class="row mb-4">
            <div class="col-12">
                <div style="background: #e7f3ff; border-left: 4px solid #17a2b8; padding: 15px; border-radius: 6px;">
                    <i class="bi bi-info-circle" style="color: #17a2b8;"></i>
                    <strong>Information:</strong> Below is the current available blood inventory.
                    <a href="blood_request.php" class="ms-2" style="text-decoration: underline; color: #0066cc;">Create a blood request</a> if you need blood.
                </div>
            </div>
        </div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-box"></i> Available Stock</h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <?php foreach ($blood_inventory as $item): 
                $is_compatible = in_array($item['blood_type'], $compatible_types);
                $is_low = $item['quantity'] < 1000;
            ?>
            <div class="col-md-3">
                <div class="card h-100 border-0 bg-light shadow-none <?php echo $is_compatible ? 'border-start border-4 border-success' : ''; ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold fs-5"><?php echo $item['blood_type']; ?></span>
                            <?php if ($is_compatible): ?>
                                <span class="badge bg-success small">Compatible</span>
                            <?php endif; ?>
                        </div>
                        <div class="h4 mb-1 <?php echo ($is_low && $item['quantity'] > 0) ? 'text-warning' : ($item['quantity'] == 0 ? 'text-danger' : 'text-primary'); ?>">
                            <?php echo $item['quantity']; ?> <small class="fs-6">ml</small>
                        </div>
                        <?php if ($is_low && $item['quantity'] > 0): ?>
                            <div class="small text-warning fw-bold">Low Stock</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-clock-history"></i> Transfusion History</h6>
    </div>
    <div class="table-container mb-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Volume</th>
                    <th>Date</th>
                    <th>Reason</th>
                    <th class="text-end">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($transfusion_history && $transfusion_history->num_rows > 0): ?>
                    <?php while ($history = $transfusion_history->fetch_assoc()): ?>
                    <tr>
                        <td class="fw-bold text-danger"><?php echo $history['blood_type']; ?></td>
                        <td class="fw-bold"><?php echo $history['units_received']; ?> <small class="text-muted">ml</small></td>
                        <td><small><?php echo date('M d, Y', strtotime($history['transfusion_date'])); ?></small></td>
                        <td class="small text-muted"><?php echo htmlspecialchars($history['transfusion_reason']); ?></td>
                        <td class="text-end">
                            <span class="badge bg-<?php echo ($history['status'] === 'completed') ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($history['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No transfusion history found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card border-0 bg-primary text-white p-4 text-center">
    <h5 class="mb-3">Need Blood Immediately?</h5>
    <p class="small opacity-75 mb-3">Submit a request and we'll match you with available donors in your area.</p>
    <div>
        <a href="blood_request.php" class="btn btn-light px-4">Request Blood Now</a>
    </div>
</div>
    </div>

<?php include 'includes/footer_recipient.php'; ?>