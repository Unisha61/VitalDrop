<?php
include 'includes/header_recipient.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Get matched donors
$matched = $conn->query("SELECT * FROM users WHERE role='donor' AND blood_type='" . escape_db_input($user['blood_type'] ?? '') . "'");
$matched_count = $matched->num_rows;

// Get blood requests
$requests = $conn->query("SELECT * FROM blood_requests WHERE recipient_id=$user_id ORDER BY created_at DESC");
$request_count = $requests->num_rows;

// Count active requests
$active_req = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE recipient_id=$user_id AND status IN ('pending', 'approved')");
$active_count = $active_req->fetch_assoc()['count'];

// Get blood availability for recipient's blood type
$inventory = $conn->query("SELECT quantity FROM blood_inventory WHERE blood_type='" . escape_db_input($user['blood_type'] ?? '') . "'");
$inventory_units = ($inventory && $inventory->num_rows > 0) ? $inventory->fetch_assoc()['quantity'] : 0;

// Get urgent requests (from last 7 days)
$urgent = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE recipient_id=$user_id AND status='pending' AND DATEDIFF(CURDATE(), DATE(created_at)) <= 7");
$urgent_count = $urgent->fetch_assoc()['count'] ?? 0;

// Get units received stats
$units_received = $conn->query("SELECT SUM(units_received) as total FROM transfusion_history WHERE recipient_id=$user_id AND status='completed'");
$units_received_total = $units_received->fetch_assoc()['total'] ?? 0;

// Get fulfilled requests count
$fulfilled = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE recipient_id=$user_id AND status='fulfilled'");
$fulfilled_count = $fulfilled->fetch_assoc()['count'] ?? 0;

// Determine urgency level
$urgency = 'Normal';
$urgency_class = 'secondary';
if ($urgent_count > 0) {
    $urgency = 'URGENT';
    $urgency_class = 'danger';
}

// Get latest blood request
$latest_req = $conn->query("SELECT * FROM blood_requests WHERE recipient_id=$user_id ORDER BY created_at DESC LIMIT 1");
$latest_request = ($latest_req && $latest_req->num_rows > 0) ? $latest_req->fetch_assoc() : null;
?>



<!-- Welcome Header -->
<div class="dashboard-header mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-heart-handshake-fill display-4"></i>
            </div>
            <div>
                <h2 class="mb-1 fw-bold">Welcome Back, <?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p class="mb-0 opacity-75">You're connected with <?php echo $matched_count; ?> compatible donors ready to help. Let's find the lifeline you need!</p>
            </div>
        </div>
    </div>
</div>

<div class="container">

<!-- Statistics Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-2 col-6">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-droplet-fill"></i></div>
            <div class="stat-label">Needed</div>
            <div class="stat-value"><?php echo htmlspecialchars($user['blood_type'] ?? 'N/A'); ?></div>
        </div>
    </div>
    
    <div class="col-md-2 col-6">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-box"></i></div>
            <div class="stat-label">Available</div>
            <div class="stat-value"><?php echo $inventory_units; ?></div>
        </div>
    </div>
    
    <div class="col-md-2 col-6">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-chat-left-text"></i></div>
            <div class="stat-label">Active</div>
            <div class="stat-value"><?php echo $active_count; ?></div>
        </div>
    </div>
    
    <div class="col-md-2 col-6">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-label">Matched</div>
            <div class="stat-value"><?php echo $matched_count; ?></div>
        </div>
    </div>
    
    <div class="col-md-2 col-6">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stat-label">Received</div>
            <div class="stat-value"><?php echo $units_received_total; ?></div>
        </div>
    </div>
    
    <div class="col-md-2 col-6">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-file-earmark-check"></i></div>
            <div class="stat-label">Fulfilled</div>
            <div class="stat-value"><?php echo $fulfilled_count; ?></div>
        </div>
    </div>
</div>

<!-- Status Section -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4 border-start border-primary border-4">
            <div class="fw-bold text-dark mb-3"><i class="bi bi-exclamation-diamond me-2 text-danger"></i> Urgency Level</div>
            <div class="badge <?php echo $urgent_count > 0 ? 'bg-danger text-white' : 'bg-primary-subtle text-primary'; ?> p-2 fs-6">
                <?php echo $urgency; ?>
            </div>
            <small class="d-block mt-3 text-muted">Based on recent requests</small>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="fw-bold text-dark mb-3"><i class="bi bi-clock-history me-2 text-primary"></i> Total Requests</div>
            <div class="text-primary fw-bold display-6">
                <?php echo $request_count; ?> <span class="fs-6 text-muted">Requests</span>
            </div>
            <small class="d-block mt-3 text-muted">All time records</small>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="fw-bold text-dark mb-3"><i class="bi bi-file-text me-2 text-primary"></i> Last Request</div>
            <?php if ($latest_request): ?>
                <div class="fw-bold h5 mb-1"><?php echo date('M d, Y', strtotime($latest_request['created_at'])); ?></div>
                <span class="badge <?php echo match($latest_request['status']) {
                    'pending' => 'bg-warning-subtle text-warning',
                    'approved' => 'bg-primary-subtle text-primary',
                    'donor_accepted' => 'bg-success-subtle text-success',
                    'fulfilled' => 'bg-info-subtle text-info',
                    'rejected' => 'bg-danger-subtle text-danger',
                    default => 'bg-secondary-subtle text-secondary'
                }; ?> px-3 py-2 mt-2">
                    <?php echo ucwords(str_replace('_', ' ', $latest_request['status'])); ?>
                </span>
            <?php else: ?>
                <div class="text-muted">No requests yet</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Recent Blood Requests -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom border-light">
        <div class="d-flex align-items-center">
            <i class="bi bi-clipboard-list me-2 fs-5 text-primary"></i>
            <h5 class="mb-0 fw-bold text-dark">Your Recent Blood Requests</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <?php 
        $recent_reqs = $conn->query("SELECT * FROM blood_requests WHERE recipient_id=$user_id ORDER BY created_at DESC LIMIT 5");
        if ($recent_reqs && $recent_reqs->num_rows > 0): ?>
            <div class="list-group list-group-flush">
                <?php while ($req = $recent_reqs->fetch_assoc()): ?>
                <div class="list-group-item p-4 border-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="flex-grow-1">
                            <div class="fw-bold mb-1 text-dark">
                                <i class="bi bi-calendar text-primary me-2"></i> <?php echo date('F d, Y', strtotime($req['created_at'])); ?>
                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-box me-1 text-primary"></i> <?php echo $req['units_needed']; ?> ml • 
                                <span class="badge <?php echo match($req['urgency']) {
                                    'critical' => 'bg-danger',
                                    'high' => 'bg-warning text-dark',
                                    'normal' => 'bg-info text-white',
                                    default => 'bg-secondary'
                                }; ?> p-1 px-2" style="font-size: 0.65rem;">
                                    <?php echo strtoupper($req['urgency']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge <?php echo match($req['status']) {
                                'pending' => 'bg-warning-subtle text-warning',
                                'approved' => 'bg-primary-subtle text-primary',
                                'donor_accepted' => 'bg-success-subtle text-success',
                                'fulfilled' => 'bg-info-subtle text-info',
                                'rejected' => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary'
                            }; ?> px-3 py-2">
                                <?php echo ucwords(str_replace('_', ' ', $req['status'])); ?>
                            </span>
                            <a href="manage_request.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-outline-danger shadow-sm px-3">
                                <i class="bi bi-arrow-right me-1"></i> View
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3"><i class="bi bi-inbox display-4 text-muted opacity-25"></i></div>
                <p class="text-muted mb-0">No blood requests yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="card border-0 shadow-sm mb-5">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4 text-dark"><i class="bi bi-lightning-fill text-warning me-2"></i> Quick Actions</h5>
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <a href="matched_donors.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-people fs-3"></i>
                    <span class="fw-bold small text-uppercase">Find Donors</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="blood_request.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-droplet-fill fs-3"></i>
                    <span class="fw-bold small text-uppercase">Request Blood</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="blood_inventory_view_recipient.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-box fs-3"></i>
                    <span class="fw-bold small text-uppercase">View Inventory</span>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="medical_history.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-file-earmark-medical fs-3"></i>
                    <span class="fw-bold small text-uppercase">Medical History</span>
                </a>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer_recipient.php'; ?>
