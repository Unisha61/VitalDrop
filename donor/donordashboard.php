<?php
include 'includes/header_donor.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Ensure 'full_name' exists in the session before accessing it
$full_name = $_SESSION['full_name'] ?? 'Donor';

// Fetch donor statistics
$donations = $conn->query("SELECT * FROM donations WHERE donor_id=$user_id ORDER BY donation_date DESC");
$donation_count = $donations->num_rows;

$units_result = $conn->query("SELECT SUM(units) as total_units FROM donations WHERE donor_id=$user_id AND status='completed'");
$total_units = $units_result->fetch_assoc()['total_units'] ?? 0;

$last_donation = $conn->query("SELECT donation_date FROM donations WHERE donor_id=$user_id ORDER BY donation_date DESC LIMIT 1");
$last_donation_date = ($last_donation && $last_donation->num_rows > 0) ? $last_donation->fetch_assoc()['donation_date'] : 'Never';

// Fetch latest health record
$health_res = $conn->query("SELECT * FROM health_records WHERE donor_id=$user_id ORDER BY recorded_date DESC LIMIT 1");
$health_data = ($health_res && $health_res->num_rows > 0) ? $health_res->fetch_assoc() : null;

// Check comprehensive eligibility (56 days + health records)
$last_donation_date_for_logic = ($last_donation_date !== 'Never') ? $last_donation_date : null;
$eligibility = getDonorEligibility($user, $health_data, $last_donation_date_for_logic);

$is_eligible = ($eligibility['status'] === 'eligible');

if ($eligibility['status'] === 'eligible') {
    $eligibility_status = '✓ Eligible to donate';
} elseif ($eligibility['status'] === 'restricted') {
    $eligibility_status = '⚠️ Temporarily restricted';
} else {
    $eligibility_status = '✗ Not eligible';
}

$eligibility_messages = $eligibility['messages'];

$pending_appointments = $conn->query("SELECT COUNT(*) as count FROM appointments WHERE donor_id=$user_id AND status='pending'");
$pending_count = $pending_appointments->fetch_assoc()['count'];

$inventory = $conn->query("SELECT blood_type, quantity FROM blood_inventory WHERE blood_type='" . $conn->real_escape_string($user['blood_type']) . "'");
$inventory_data = ($inventory && $inventory->num_rows > 0) ? $inventory->fetch_assoc()['quantity'] : 0;

$upcoming = $conn->query("SELECT * FROM appointments WHERE donor_id=$user_id AND status IN ('pending', 'confirmed') ORDER BY appointment_date ASC LIMIT 3");

$direct_requests = $conn->query("
    SELECT br.*, u.full_name as recipient_name, u.email as recipient_email, u.phone as recipient_phone 
    FROM blood_requests br 
    JOIN users u ON br.recipient_id = u.id 
    WHERE br.donor_id = $user_id AND br.status = 'approved' 
    ORDER BY br.created_at DESC
");
?>



<!-- Welcome Header -->
<div class="dashboard-header mb-4">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-heart-pulse-fill display-4"></i>
            </div>
            <div>
                <h2 class="mb-1 fw-bold">Welcome Back, <?php echo htmlspecialchars($full_name); ?></h2>
                <p class="mb-0 opacity-75">Thank you for being a lifesaver. Your donations make a real difference.</p>
            </div>
        </div>
    </div>
</div>

<div class="container">

<!-- Statistics Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-droplet-fill"></i></div>
            <div class="stat-label">Total Donations</div>
            <div class="stat-value"><?php echo $donation_count; ?></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
            <div class="stat-label">Blood Donated</div>
            <div class="stat-value"><?php echo $total_units; ?> <span class="fs-6">ml</span></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card h-100 shadow-sm text-center">
            <div class="stat-icon"><i class="bi bi-droplet"></i></div>
            <div class="stat-label">Blood Type</div>
            <div class="stat-value"><?php echo htmlspecialchars($user['blood_type'] ?? 'N/A'); ?></div>
        </div>
    </div>
</div>

<!-- Status Section -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4 <?php echo $is_eligible ? 'border-start border-success border-4' : ($eligibility['status'] === 'restricted' ? 'border-start border-warning border-4' : 'border-start border-danger border-4'); ?>">
            <div class="fw-bold text-dark mb-3"><i class="bi bi-calendar-check me-2"></i> Donation Eligibility</div>
            <div class="badge <?php echo $is_eligible ? 'bg-success-subtle text-success' : ($eligibility['status'] === 'restricted' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger'); ?> p-2 mb-2 fs-6 text-wrap">
                <?php echo $eligibility_status; ?>
            </div>
            <?php if (!empty($eligibility_messages)): ?>
                <div class="text-danger small mt-1 fw-bold">
                    <?php echo htmlspecialchars($eligibility_messages[0]); ?>
                </div>
            <?php endif; ?>
            <div class="text-muted small mt-2">Last: <?php echo $last_donation_date !== 'Never' ? date('M d, Y', strtotime($last_donation_date)) : 'Never'; ?></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="fw-bold text-dark mb-3"><i class="bi bi-inbox me-2"></i> Blood In Inventory</div>
            <div class="text-primary fw-bold display-6">
                <?php echo $inventory_data; ?> <span class="fs-6 text-muted">ml</span>
            </div>
            <div class="text-muted small mt-2">Your blood type: <?php echo htmlspecialchars($user['blood_type'] ?? 'N/A'); ?></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm p-4">
            <div class="fw-bold text-dark mb-3"><i class="bi bi-clock me-2"></i> Pending Pledges</div>
            <div class="text-primary fw-bold display-6">
                <?php echo $pending_count; ?>
            </div>
            <div class="text-muted small mt-2">Awaiting confirmation</div>
        </div>
    </div>
</div>

<!-- Direct Blood Requests Section -->
<?php if ($direct_requests && $direct_requests->num_rows > 0): ?>
<div class="card border-danger border-2 mb-4 shadow">
    <div class="card-header bg-danger text-white py-3">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>
            <h5 class="mb-0 fw-bold">Direct Blood Requests For You!</h5>
        </div>
    </div>
    <div class="card-body">
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="bi bi-bell-fill me-2 fs-5"></i> 
            <div>You have been specifically requested to donate blood! An Admin has approved these requests.</div>
        </div>
        
        <div class="list-group list-group-flush">
        <?php while($req = $direct_requests->fetch_assoc()): ?>
            <div class="list-group-item px-0 py-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-person-fill text-danger me-2"></i> <?php echo htmlspecialchars($req['recipient_name']); ?></h6>
                        <div class="text-danger fw-bold mb-1">
                            <i class="bi bi-droplet-fill me-1"></i> Needs: <?php echo $req['units_needed']; ?> ml (Urgency: <?php echo ucfirst($req['urgency']); ?>)
                        </div>
                        <?php if($req['medical_reason']): ?>
                            <div class="text-muted small mb-1"><i class="bi bi-clipboard-pulse me-1"></i> Reason: <?php echo htmlspecialchars($req['medical_reason']); ?></div>
                        <?php endif; ?>
                        <div class="text-muted small">
                            <i class="bi bi-telephone-fill me-1"></i> <?php echo htmlspecialchars($req['recipient_phone'] ?? 'No phone'); ?>
                        </div>
                    </div>
                    <form action="handle_blood_request.php" method="POST" class="m-0">
                        <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                        <button type="submit" class="btn btn-danger px-4 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Accept Request
                        </button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Appointments -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom border-light">
        <div class="d-flex align-items-center">
            <i class="bi bi-calendar-event me-2 fs-5 text-primary"></i>
            <h5 class="mb-0 fw-bold text-dark">Active Availability Pledges</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if ($upcoming && $upcoming->num_rows > 0): ?>
            <div class="list-group list-group-flush">
                <?php while($apt = $upcoming->fetch_assoc()): ?>
                <div class="list-group-item p-4 border-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="fw-bold mb-1"><i class="bi bi-person-check-fill text-primary me-2"></i> <?php echo $apt['appointment_date'] ? date('M d, Y', strtotime($apt['appointment_date'])) : 'Available Now (Real-Time)'; ?></div>
                            <?php if ($apt['appointment_time']): ?>
                            <div class="text-muted small"><i class="bi bi-clock me-1"></i> <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></div>
                            <?php endif; ?>
                            <div class="mt-2"><span class="badge <?php echo $apt['status'] === 'pending' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'; ?> px-3 py-2"><?php echo ucfirst($apt['status']); ?></span></div>
                        </div>
                        <?php if ($apt['status'] === 'confirmed'): ?>
                        <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#donationModal" 
                                onclick="document.getElementById('modalAptId').value = <?php echo $apt['id']; ?>">
                            Mark Complete
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3"><i class="bi bi-person-slash display-4 text-muted opacity-25"></i></div>
                <p class="text-muted mb-0">You have not marked yourself as available.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Donation History Section -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom border-light">
        <div class="d-flex align-items-center">
            <i class="bi bi-droplet-half me-2 fs-5 text-warning"></i>
            <h5 class="mb-0 fw-bold text-dark">Recent Donations</h5>
        </div>
    </div>
    <div class="card-body p-0">
        <?php
        $completed_donations = $conn->query("SELECT * FROM donations WHERE donor_id=$user_id AND status='completed' ORDER BY donation_date DESC LIMIT 5");
        if ($completed_donations && $completed_donations->num_rows > 0): ?>
            <div class="list-group list-group-flush">
                <?php while ($donation = $completed_donations->fetch_assoc()): ?>
                <div class="list-group-item p-4 border-light">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <div class="fw-bold mb-1"><i class="bi bi-calendar3 text-primary me-2"></i> <?php echo date('M d, Y', strtotime($donation['donation_date'])); ?></div>
                            <div class="text-muted small mb-2"><i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($donation['time'] ?? 'N/A'); ?></div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-success-subtle text-success">Completed</span>
                                <span class="badge bg-info-subtle text-info"><?php echo htmlspecialchars($donation['blood_type']); ?></span>
                                <span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($donation['units']); ?> ml</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3"><i class="bi bi-inbox display-4 text-muted opacity-25"></i></div>
                <p class="text-muted mb-0">No completed donations yet</p>
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
                <a href="appointment.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-person-check-fill fs-3"></i>
                    <span class="fw-bold small text-uppercase">Mark Available</span>
                </a>
            </div>
        
            <div class="col-md-3 col-6">
                <a href="health_record.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-heart-pulse fs-3"></i>
                    <span class="fw-bold small text-uppercase">Health Record</span>
                </a>
            </div>
        
            <div class="col-md-3 col-6">
                <a href="donation.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-droplet-fill fs-3"></i>
                    <span class="fw-bold small text-uppercase">Donation History</span>
                </a>
            </div>
        
            <div class="col-md-3 col-6">
                <a href="edit_profile.php" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2">
                    <i class="bi bi-person-gear fs-3"></i>
                    <span class="fw-bold small text-uppercase">Edit Profile</span>
                </a>
            </div>
        </div>
    </div>
</div>
</div>

<?php include 'includes/footer_donor.php'; ?>

<!-- Donation Amount Modal -->
<div class="modal fade" id="donationModal" tabindex="-1" aria-labelledby="donationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="donationModalLabel">Complete Donation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="mark_donation_completed.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="donationDate" class="form-label"><strong>Date of Donation</strong></label>
                        <input type="date" class="form-control" id="donationDate" name="donation_date" 
                               max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                        <small class="form-text text-muted d-block mt-2">
                            Select the date the blood was actually drawn.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="donatedMl" class="form-label"><strong>Donation Amount (in ml)</strong></label>
                        <input type="number" class="form-control" id="donatedMl" name="donated_ml" 
                               min="200" max="500" value="450" required>
                        <small class="form-text text-muted d-block mt-2">
                            <strong>Hint:</strong> A standard donation is around <strong>450 ml</strong>. 
                            Acceptable range is 200-500 ml.
                        </small>
                    </div>
                    <input type="hidden" name="appointment_id" id="modalAptId" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Donation</button>
                </div>
            </form>
        </div>
    </div>
</div>
