<?php
include 'includes/header_recipient.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Handle cancel request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $request_id = (int)$_POST['request_id'];
    $cancel_reason = sanitize_input($_POST['cancel_reason'] ?? '');
    
    $update_sql = "UPDATE blood_requests 
                  SET status='cancelled', cancel_reason='" . escape_db_input($cancel_reason) . "'
                  WHERE id=$request_id AND recipient_id=$user_id AND status='pending'";
    
    if ($conn->query($update_sql)) {
        $message = '✓ Request cancelled successfully!';
        $message_type = 'success';
    }
}

// Get all blood requests
$requests = $conn->query("SELECT br.*, u.full_name as donor_name, u.phone, u.email 
                         FROM blood_requests br 
                         LEFT JOIN users u ON br.donor_id = u.id 
                         WHERE br.recipient_id=$user_id 
                         ORDER BY br.created_at DESC");
?>



<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="request-stat primary">
            <div>Total Requests</div>
            <h3><?php echo $requests->num_rows ?? 0; ?></h3>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="request-stat warning">
            <div>Pending</div>
            <h3>
                <?php 
                $pending = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE recipient_id=$user_id AND status='pending'");
                echo $pending->fetch_assoc()['count'] ?? 0;
                ?>
            </h3>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="request-stat success">
            <div>Approved</div>
            <h3>
                <?php 
                $approved = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE recipient_id=$user_id AND status='approved'");
                echo $approved->fetch_assoc()['count'] ?? 0;
                ?>
            </h3>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="request-stat info">
            <div>Fulfilled</div>
            <h3>
                <?php 
                $fulfilled = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE recipient_id=$user_id AND status='fulfilled'");
                echo $fulfilled->fetch_assoc()['count'] ?? 0;
                ?>
            </h3>
        </div>
    </div>
</div>

<div class="requests-container">
    <h5 class="mb-4"><i class="bi bi-list-ul"></i> Blood Request History</h5>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert" style="border-radius: 10px; border: none;">
                <i class="bi bi-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($requests && $requests->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Donor Name</th>
                            <th>Units</th>
                            <th>Urgency</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = $requests->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <small><?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php 
                                echo !empty($req['donor_name']) 
                                    ? htmlspecialchars($req['donor_name']) 
                                    : '<em class="text-muted">Unassigned</em>';
                                ?>
                            </td>
                            <td><span class="badge bg-info"><?php echo $req['units_needed']; ?> U</span></td>
                            <td>
                                <span class="badge bg-<?php echo match($req['urgency']) {
                                    'critical' => 'danger',
                                    'high' => 'warning',
                                    'normal' => 'info',
                                    default => 'secondary'
                                }; ?>">
                                    <?php echo ucfirst($req['urgency']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo match($req['status']) {
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'fulfilled' => 'info',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'secondary'
                                }; ?>">
                                    <?php echo ucfirst($req['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" 
                                        data-bs-target="#detailsModal<?php echo $req['id']; ?>">
                                    <i class="bi bi-eye"></i> Details
                                </button>
                                <?php if ($req['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" 
                                        data-bs-target="#cancelModal<?php echo $req['id']; ?>">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <!-- Details Modal -->
                        <div class="modal fade" id="detailsModal<?php echo $req['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-info text-white">
                                        <h5 class="modal-title">Request Details</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Request ID:</strong> #<?php echo $req['id']; ?></p>
                                        <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($req['created_at'])); ?></p>
                                        <p><strong>Units Needed:</strong> <?php echo $req['units_needed']; ?> units</p>
                                        <p><strong>Urgency:</strong> <?php echo ucfirst($req['urgency']); ?></p>
                                        <p><strong>Status:</strong> <?php echo ucfirst($req['status']); ?></p>
                                        <?php if (!empty($req['medical_reason'])): ?>
                                        <p><strong>Medical Reason:</strong> <?php echo htmlspecialchars($req['medical_reason']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($req['donor_name'])): ?>
                                        <hr>
                                        <p><strong>Assigned Donor:</strong> <?php echo htmlspecialchars($req['donor_name']); ?></p>
                                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($req['email'] ?? 'N/A'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cancel Modal -->
                        <?php if ($req['status'] === 'pending'): ?>
                        <div class="modal fade" id="cancelModal<?php echo $req['id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger text-white">
                                        <h5 class="modal-title">Cancel Request</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <p>Are you sure you want to cancel this blood request?</p>
                                            <div class="mb-3">
                                                <label class="form-label">Reason (Optional)</label>
                                                <textarea class="form-control" name="cancel_reason" rows="2" 
                                                          placeholder="Tell us why you're cancelling..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Request</button>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="bi bi-trash"></i> Cancel Request
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                <p class="mt-3">You haven't made any blood requests yet.</p>
                <a href="matched_donors.php" class="btn btn-primary">Find Donors & Make a Request</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-3">
    <a href="recipientdashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include 'includes/footer_recipient.php'; ?>
