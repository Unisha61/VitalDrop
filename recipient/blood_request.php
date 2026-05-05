<?php
include 'includes/header_recipient.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$message = '';
$message_type = '';

// Handle blood request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_blood') {
    $blood_type = $conn->real_escape_string(trim($_POST['blood_type'] ?? ''));
    $units_needed = (int)($_POST['units_needed'] ?? 0);
    $urgency = $conn->real_escape_string(trim($_POST['urgency'] ?? 'normal'));
    $medical_reason = $conn->real_escape_string(trim($_POST['medical_reason'] ?? ''));

    if (!empty($blood_type) && $units_needed > 0 && !empty($urgency)) {
        $insert_query = "INSERT INTO blood_requests (recipient_id, blood_type, units_needed, urgency, medical_reason, status) 
                        VALUES ($user_id, '$blood_type', $units_needed, '$urgency', '$medical_reason', 'pending')";
        
        if ($conn->query($insert_query)) {
            $message = '✓ Blood request submitted successfully! Admin will review it shortly.';
            $message_type = 'success';
        } else {
            $message = '✗ Failed to submit request: ' . $conn->error;
            $message_type = 'error';
        }
    } else {
        $message = '✗ Please fill all required fields correctly.';
        $message_type = 'error';
    }
}

// Get pending and approved requests with donor info
$pending_requests = $conn->query("
    SELECT 
        br.*,
        u.full_name as donor_name
    FROM blood_requests br
    LEFT JOIN users u ON br.donor_id = u.id
    WHERE br.recipient_id=$user_id AND br.status IN ('pending', 'approved') 
    ORDER BY br.created_at DESC
");

// Get inventory
$inventory = $conn->query("SELECT * FROM blood_inventory ORDER BY blood_type ASC");
?>
<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-droplet-fill"></i> Blood Request</h2>
        <p class="text-muted small mb-0">Submit an urgent request for blood units.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Blood Request Form -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-droplet"></i> Request Blood</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">Submit a request and our team will process it based on availability and urgency.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> py-2 small mb-4"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="request_blood">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Blood Type Required*</label>
                        <select class="form-select" name="blood_type" required>
                            <option value="">Select Blood Type</option>
                            <?php foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $type): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Amount Needed (ml)*</label>
                        <input type="number" class="form-control" name="units_needed" min="200" max="500" value="450" required>
                        <small class="text-muted d-block mt-1 small">Standard is 450 ml (Range: 200-500 ml)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Urgency Level*</label>
                        <select class="form-select" name="urgency" required>
                            <option value="normal">Normal - Non-urgent</option>
                            <option value="high">High - Urgent</option>
                            <option value="critical">Critical - Emergency</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Medical Reason*</label>
                        <textarea class="form-control" name="medical_reason" rows="3" required placeholder="e.g., Surgery, Transfusion..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send"></i> Submit Request</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Inventory Overview -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-box-seam"></i> Available Inventory</h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    <?php 
                    $inventory->data_seek(0);
                    while ($item = $inventory->fetch_assoc()): ?>
                        <div class="col-6">
                            <div class="p-2 border rounded text-center">
                                <div class="fw-bold text-danger"><?php echo $item['blood_type']; ?></div>
                                <div class="small text-muted"><?php echo $item['quantity']; ?> ml</div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

    <!-- Active Requests -->
    <div class="col-12 mt-2">
        <h5 class="fw-bold mb-3 px-1"><i class="bi bi-clock-history"></i> Your Recent Requests</h5>
        <?php if ($pending_requests && $pending_requests->num_rows > 0): ?>
            <div class="row g-3">
                <?php while ($request = $pending_requests->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm border-start border-4 border-<?php echo ($request['status'] === 'approved') ? 'success' : 'warning'; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-danger"><i class="bi bi-droplet-fill"></i> <?php echo $request['blood_type']; ?></span>
                                <span class="badge bg-<?php echo ($request['status'] === 'approved') ? 'success' : 'warning'; ?>"><?php echo ucfirst($request['status']); ?></span>
                            </div>
                            <div class="text-muted small mb-3">
                                <div><strong>Volume:</strong> <?php echo $request['units_needed']; ?> ml</div>
                                <div><strong>Urgency:</strong> <span class="text-<?php echo $request['urgency'] === 'critical' ? 'danger' : ($request['urgency'] === 'high' ? 'warning' : 'success'); ?> fw-bold"><?php echo ucfirst($request['urgency']); ?></span></div>
                                <div><strong>Date:</strong> <?php echo date('M d, Y', strtotime($request['created_at'])); ?></div>
                            </div>
                            <?php if ($request['status'] === 'approved'): ?>
                                <button type="button" class="btn btn-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#receivedModal<?php echo $request['id']; ?>">
                                    <i class="bi bi-check-circle"></i> Mark Received
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Modal Integration -->
                <?php if ($request['status'] === 'approved'): ?>
                <div class="modal fade" id="receivedModal<?php echo $request['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white py-3">
                                <h5 class="modal-title fw-bold">Confirm Receipt</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="mark_blood_received.php">
                                <div class="modal-body p-4">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Actual Amount Received (ml)</label>
                                        <input type="number" class="form-control" name="received_ml" min="100" max="1000" value="<?php echo $request['units_needed']; ?>" required>
                                        <div class="form-text mt-2 small">Enter the final volume received from the donor.</div>
                                    </div>
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                </div>
                                <div class="modal-footer border-0 p-3">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary px-4">Save Confirmation</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="card bg-light border-0 py-5 text-center">
                <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
                <p class="text-muted">No active blood requests found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<?php include 'includes/footer_recipient.php'; ?>
