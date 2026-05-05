<?php
include 'includes/header_admin.php';

$message = '';
$message_type = '';

// Handle approve/reject/fulfill request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $request_id = (int)($_POST['request_id'] ?? 0);
    $reject_reason = $conn->real_escape_string(trim($_POST['reject_reason'] ?? ''));
    $received_ml = isset($_POST['received_ml']) ? (int)$_POST['received_ml'] : 0;
    
    if ($request_id > 0) {
        if ($action === 'approve') {
            $update_sql = "UPDATE blood_requests SET status = 'approved' WHERE id = $request_id";
            if ($conn->query($update_sql)) {
                $message = '✓ Request approved!';
                $message_type = 'success';
            }
        } elseif ($action === 'reject') {
            $update_sql = "UPDATE blood_requests SET status = 'rejected', cancel_reason = '$reject_reason' WHERE id = $request_id";
            if ($conn->query($update_sql)) {
                $message = '✓ Request rejected!';
                $message_type = 'success';
            }
        } elseif ($action === 'fulfill') {
            // Validate received amount
            if ($received_ml < 100 || $received_ml > 500) {
                $message = '✗ Received amount must be between 100 and 500 ml!';
                $message_type = 'danger';
            } else {
                // Get the request details with recipient's blood type
                $get_request = $conn->query("SELECT br.*, u.blood_type FROM blood_requests br JOIN users u ON br.recipient_id = u.id WHERE br.id = $request_id");
                if ($get_request && $get_request->num_rows > 0) {
                    $req = $get_request->fetch_assoc();
                    
                    // Check if enough blood is available
                    $blood_check = $conn->query("SELECT quantity FROM blood_inventory WHERE blood_type = '" . escape_db_input($req['blood_type']) . "'");
                    if ($blood_check && $blood_check->num_rows > 0) {
                        $blood = $blood_check->fetch_assoc();
                        if ($blood['quantity'] >= $received_ml) {
                            // Update blood inventory with actual received amount
                            $conn->query("UPDATE blood_inventory SET quantity = quantity - $received_ml WHERE blood_type = '" . escape_db_input($req['blood_type']) . "'");
                            // Mark request as fulfilled
                            $conn->query("UPDATE blood_requests SET status = 'fulfilled' WHERE id = $request_id");
                            // Insert into transfusion history (tracks the actual units received)
                            $conn->query("INSERT INTO transfusion_history (recipient_id, blood_type, units_received, transfusion_date, transfusion_reason, status) 
                                        VALUES ({$req['recipient_id']}, '{$req['blood_type']}', $received_ml, NOW(), '" . $conn->real_escape_string($req['medical_reason']) . "', 'completed')");
                            $message = "✓ Request fulfilled! Received: $received_ml ml. Inventory updated!";
                            $message_type = 'success';
                        } else {
                            $message = "✗ Insufficient blood inventory! Available: {$blood['quantity']} ml, Requested: $received_ml ml";
                            $message_type = 'danger';
                        }
                    }
                }
            }
        }
    }
}

// Get filter parameters
$filter_status = $_GET['status'] ?? 'all';
$sort_by = $_GET['sort'] ?? 'id';

// Build query
$where_clause = '';
if ($filter_status !== 'all') {
    $where_clause = " WHERE status = '" . escape_db_input($filter_status) . "'";
}

$order_clause = ' ORDER BY ';
if ($sort_by === 'id') {
    $order_clause .= 'id DESC';
} elseif ($sort_by === 'date') {
    $order_clause .= 'created_at DESC';
} elseif ($sort_by === 'blood_type') {
    $order_clause .= 'blood_type ASC';
} else {
    $order_clause .= 'id DESC';
}

$requests_sql = "SELECT br.*, u.full_name, u.email, u.phone, u.blood_type FROM blood_requests br 
                 JOIN users u ON br.recipient_id = u.id 
                 $where_clause 
                 $order_clause";

$requests_result = $conn->query($requests_sql);

// Get statistics
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'fulfilled' THEN 1 ELSE 0 END) as fulfilled,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
FROM blood_requests";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>



<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-droplet-fill"></i> Blood Request Management</h2>
        <p class="text-muted small mb-0">Manage and process recipient blood requests.</p>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> py-2 small mb-4">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-list-task"></i></div>
        <div class="stat-label">Total Requests</div>
        <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-warning"><i class="bi bi-clock"></i></div>
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?php echo $stats['pending'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-label">Approved</div>
        <div class="stat-value"><?php echo $stats['approved'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="bi bi-gift"></i></div>
        <div class="stat-label">Fulfilled</div>
        <div class="stat-value"><?php echo $stats['fulfilled'] ?? 0; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-x-circle"></i></div>
        <div class="stat-label">Rejected</div>
        <div class="stat-value"><?php echo $stats['rejected'] ?? 0; ?></div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-table"></i> Request Log</h6>
    </div>
    <div class="table-container mb-0">
        <?php if ($requests_result && $requests_result->num_rows > 0): ?>
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Recipient</th>
                        <th>Blood</th>
                        <th>Volume</th>
                        <th>Urgency</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sn = 1;
                    while ($row = $requests_result->fetch_assoc()): 
                        $status_class = match($row['status']) {
                            'pending' => 'warning',
                            'approved' => 'primary',
                            'fulfilled' => 'success',
                            'rejected' => 'danger',
                            default => 'secondary'
                        };
                    ?>
                    <tr>
                        <td><div class="fw-bold"><?php echo $sn++; ?></div></td>
                        <td>
                            <div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div>
                            <div class="small text-muted"><?php echo htmlspecialchars($row['email']); ?></div>
                        </td>
                        <td><span class="badge bg-danger"><?php echo htmlspecialchars($row['blood_type']); ?></span></td>
                        <td class="fw-bold"><?php echo $row['units_needed']; ?> ml</td>
                        <td>
                            <span class="badge bg-<?php echo ($row['urgency'] === 'critical') ? 'danger' : (($row['urgency'] === 'high') ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($row['urgency']); ?>
                            </span>
                        </td>
                        <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                        <td class="text-end">
                            <div class="btn-group">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="document.getElementById('rejectRequestId').value=<?php echo $row['id']; ?>"><i class="bi bi-x-lg"></i></button>
                                <?php endif; ?>
                                
                                <?php if ($row['status'] === 'approved'): ?>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#fulfillModal" onclick="document.getElementById('fulfillRequestId').value=<?php echo $row['id']; ?>; document.getElementById('receivedMl').value=<?php echo $row['units_needed']; ?>"><i class="bi bi-box-arrow-in-down"></i> Fulfill</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3"></i>
                No blood requests found.
            </div>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold">Reject Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="request_id" id="rejectRequestId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Rejection</label>
                        <textarea name="reject_reason" class="form-control" rows="3" required placeholder="Enter rejection reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fulfill Modal -->
<div class="modal fade" id="fulfillModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold">Fulfill Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="fulfill">
                    <input type="hidden" name="request_id" id="fulfillRequestId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Actual Amount Fulfilled (ml)</label>
                        <input type="number" name="received_ml" id="receivedMl" class="form-control" min="100" max="1000" required>
                        <div class="form-text small mt-2">Inventory will be deducted by this amount.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Confirm Fulfillment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer_admin.php'; ?>
