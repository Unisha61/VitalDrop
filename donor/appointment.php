<?php
include 'includes/header_donor.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Refresh user details dynamically
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Refresh health record dynamically
$latest_health = $conn->query("SELECT * FROM health_records WHERE donor_id=$user_id ORDER BY recorded_date DESC LIMIT 1");
$health_data = ($latest_health && $latest_health->num_rows > 0) ? $latest_health->fetch_assoc() : null;

// Calculate donation eligibility based on health
$is_health_eligible = false;
$eligibility_issues = [];
$health_check_status = '';

if (!$health_data) {
    $eligibility_issues[] = "No health record found. Please complete your health check first.";
    $health_check_status = 'missing';
} else {
    $is_health_eligible = true;
    
    // Check comprehensive health eligibility
    $eligibility = getDonorEligibility($user, $health_data, null);
    
    if ($eligibility['status'] !== 'eligible') {
        $is_health_eligible = false;
        foreach ($eligibility['messages'] as $msg) {
            if (strpos($msg, '56-day') === false) {
                $eligibility_issues[] = $msg;
            }
        }
    }
    
    $health_check_status = $is_health_eligible ? 'eligible' : 'ineligible';
}

// NEW: Check 56-day donation interval eligibility
$is_interval_eligible = true;
$next_eligible_date = null;
$last_donation_result = $conn->query("SELECT donation_date FROM donations WHERE donor_id=$user_id AND status='completed' ORDER BY donation_date DESC LIMIT 1");

if ($last_donation_result && $last_donation_result->num_rows > 0) {
    $last_donation = $last_donation_result->fetch_assoc();
    $last_donation_date = strtotime($last_donation['donation_date']);
    $eligible_date = strtotime('+56 days', $last_donation_date);
    $today = strtotime(date('Y-m-d'));
    
    if ($today < $eligible_date) {
        $is_interval_eligible = false;
        $next_eligible_date = date('Y-m-d', $eligible_date);
        $days_remaining = ceil(($eligible_date - $today) / (60 * 60 * 24));
        $eligibility_issues[] = "56-day donation interval not met. Last donation: " . date('M d, Y', $last_donation_date) . ". Eligible again: $next_eligible_date ($days_remaining days remaining)";
    }
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    // Check both health AND interval eligibility before allowing booking
    if (!$is_health_eligible || !$is_interval_eligible) {
        $message = '✗ Cannot book appointment: ' . implode(', ', $eligibility_issues);
        $message_type = 'danger';
    } else {
        $reason = sanitize_input($_POST['reason'] ?? '');
        
        $insert_sql = "INSERT INTO appointments (donor_id, appointment_date, appointment_time, reason, status, created_at) 
                      VALUES ($user_id, NULL, NULL, '" . escape_db_input($reason) . "', 'pending', NOW())";
        
        if ($conn->query($insert_sql)) {
            $message = '✓ Successfully marked as Available to Donate! Pending admin confirmation.';
            $message_type = 'success';
        } else {
            $message = '✗ Error marking availability!';
            $message_type = 'danger';
        }
    }
}

// Get all appointments with status
$all_appointments = $conn->query("SELECT id, appointment_date, appointment_time, reason, status FROM appointments WHERE donor_id=$user_id ORDER BY appointment_date DESC");
?>


<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-calendar-plus-fill"></i> Donation Appointment</h2>
        <p class="text-muted small mb-0">Pledge your availability and schedule donations.</p>
    </div>
</div>
    <!-- Health Status Check -->
    <div class="col-lg-6">
        <div class="card h-100 <?php echo ($health_check_status === 'eligible') ? 'border-success' : 'border-danger'; ?>">
            <div class="card-body">
                <?php if ($health_check_status === 'eligible'): ?>
                    <h5 class="text-success mb-3"><i class="bi bi-check-circle-fill"></i> Health Check Passed</h5>
                    <p class="text-success small">Your health vitals are within acceptable ranges for blood donation.</p>
                    <?php if ($health_data): ?>
                        <div class="text-muted small mt-2">Last updated: <?php echo date('M d, Y', strtotime($health_data['recorded_date'])); ?></div>
                    <?php endif; ?>
                <?php elseif ($health_check_status === 'ineligible'): ?>
                    <h5 class="text-danger mb-3"><i class="bi bi-exclamation-circle-fill"></i> Unable to Donate</h5>
                    <p class="text-danger small mb-3">Your health vitals do not meet the requirements.</p>
                    <?php foreach ($eligibility_issues as $issue): ?>
                        <div class="alert alert-danger py-2 px-3 small mb-2"><i class="bi bi-x-circle"></i> <?php echo htmlspecialchars($issue); ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h5 class="text-warning mb-3"><i class="bi bi-exclamation-triangle-fill"></i> Health Check Required</h5>
                    <p class="text-muted small mb-4">Complete your health check before booking an appointment.</p>
                    <a href="health_record.php" class="btn btn-primary btn-sm">Go to Health Check</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Interval Eligibility -->
    <div class="col-lg-6">
        <div class="card h-100 <?php echo $is_interval_eligible ? 'border-success' : 'border-danger'; ?>">
            <div class="card-body">
                <?php if ($is_interval_eligible && $last_donation_result && $last_donation_result->num_rows > 0): ?>
                    <h5 class="text-success mb-3"><i class="bi bi-check-circle-fill"></i> Interval Requirement Met</h5>
                    <p class="text-success small">56 days have passed since your last donation.</p>
                <?php elseif (!$is_interval_eligible && $next_eligible_date): ?>
                    <h5 class="text-danger mb-3"><i class="bi bi-exclamation-circle-fill"></i> Waiting Period Active</h5>
                    <p class="text-danger small mb-3">You need to wait 56 days between donations.</p>
                    <div class="alert alert-danger py-2 px-3 small"><i class="bi bi-calendar-x"></i> Next eligible: <?php echo date('M d, Y', strtotime($next_eligible_date)); ?></div>
                <?php else: ?>
                    <h5 class="text-primary mb-3"><i class="bi bi-star-fill"></i> First Time Donor</h5>
                    <p class="text-muted small">Welcome! You're eligible to book your first appointment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Booking Form -->
    <div class="col-lg-6">
        <div class="card <?php if (!$is_health_eligible || !$is_interval_eligible) echo 'opacity-75'; ?>">
            <div class="card-body">
                <h5 class="mb-4"><i class="bi bi-person-check"></i> Mark Availability</h5>
                <p class="text-muted small mb-4">By pledging your availability, you'll appear on the matched donors list for recipients in need.</p>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> py-2 small"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="book">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Note / Reason (Optional)</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Any preferences or notes..." <?php if (!$is_health_eligible || !$is_interval_eligible) echo 'disabled'; ?>></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" <?php if (!$is_health_eligible || !$is_interval_eligible) echo 'disabled'; ?>>
                        <i class="bi bi-check-circle"></i> Mark Available to Donate
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pledges List -->
    <div class="col-lg-6">
        <h5 class="mb-3"><i class="bi bi-list-check"></i> Active Pledges</h5>
        <?php if ($all_appointments && $all_appointments->num_rows > 0): ?>
            <?php while($apt = $all_appointments->fetch_assoc()): ?>
            <div class="card mb-3 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1 fw-bold"><?php echo $apt['appointment_date'] ? date('M d, Y', strtotime($apt['appointment_date'])) : 'Available Now'; ?></h6>
                            <?php if ($apt['appointment_time']): ?><div class="text-muted small"><i class="bi bi-clock"></i> <?php echo $apt['appointment_time']; ?></div><?php endif; ?>
                        </div>
                        <span class="badge bg-primary"><?php echo ucfirst($apt['status']); ?></span>
                    </div>
                    <?php if (in_array($apt['status'], ['pending', 'confirmed'])): ?>
                    <div class="mt-3 pt-3 border-top">
                        <a href="manage_appointment.php?id=<?php echo $apt['id']; ?>" class="btn btn-outline-danger btn-sm">Manage</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card bg-light border-0">
                <div class="card-body text-center py-5">
                    <i class="bi bi-person-slash display-4 text-muted mb-3 d-block"></i>
                    <p class="text-muted">No active availability pledges found.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer_donor.php'; ?>
