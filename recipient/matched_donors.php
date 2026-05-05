<?php
include 'includes/header_recipient.php';
require_once '../filtering_matching.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

// Get filter parameters
$filter_donations = sanitize_input($_GET['filter_donations'] ?? '');
$search_blood_type = sanitize_input($_GET['search_blood_type'] ?? '');

// Find ALL donors with donation count
// NEW CODE: Removed ORDER BY - will sort by priority score instead
$query = "SELECT u.*, 
          (SELECT COUNT(id) FROM donations WHERE donor_id = u.id AND status='completed') as total_donations 
          FROM users u 
          WHERE u.role='donor'
          AND EXISTS (SELECT 1 FROM appointments a WHERE a.donor_id = u.id AND a.status IN ('pending', 'confirmed'))";

$all_donors_result = $conn->query($query);

// Process all donors: compatibility check, distance, and priority score
$matched_blood_types = [];
$all_donors = processDonors($all_donors_result, $user, $matched_blood_types, $conn);

// Sort donors: compatible first, then by priority score, then by experience
sortDonors($all_donors);

// Handle donor request - only allow for compatible donors
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    $donor_id = (int)$_POST['donor_id'];
    $units_needed = (int)$_POST['units_needed'];
    $urgency = sanitize_input($_POST['urgency'] ?? 'normal');
    $medical_reason = sanitize_input($_POST['medical_reason'] ?? '');
    
    // Verify donor is compatible before allowing request
    $donor_check = null;
    foreach($all_donors as $d) {
        if ($d['id'] == $donor_id) {
            $donor_check = $d;
            break;
        }
    }
    
    if ($donor_check && $donor_check['is_compatible'] && $units_needed > 0 && $units_needed <= 500) {
        $insert_sql = "INSERT INTO blood_requests (donor_id, recipient_id, units_needed, urgency, medical_reason, status, created_at)
                      VALUES ($donor_id, $user_id, $units_needed, '" . escape_db_input($urgency) . "', 
                              '" . escape_db_input($medical_reason) . "', 'pending', NOW())";
        
        if ($conn->query($insert_sql)) {
            echo "<script>alert('✓ Blood request sent to donor!'); window.location.href='blood_requests.php';</script>";
        }
    } else {
        // DEBUG: Show why request failed
        $debug_msg = '✗ Cannot request from this donor.';
        if (!$donor_check) {
            $debug_msg .= ' (Donor not found)';
        } else if (!$donor_check['is_compatible']) {
            $debug_msg .= ' Recipient: ' . htmlspecialchars($recipient_blood_type) . ', Donor: ' . htmlspecialchars($donor_check['blood_type']);
        } else if (!($units_needed > 0 && $units_needed <= 10)) {
            $debug_msg .= ' (Invalid units)';
        }
        echo "<script>alert('" . addslashes($debug_msg) . "'); </script>";
    }
}

sort($matched_blood_types);
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people"></i> Available Donors</h5>
    </div>
    <div class="card-body p-4">
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Blood Type</label>
                    <select name="search_blood_type" class="form-select">
                        <option value="">All Blood Types</option>
                        <?php foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $search_blood_type === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Experience</label>
                    <select name="filter_donations" class="form-select">
                        <option value="">All Donors</option>
                        <option value="5" <?php echo $filter_donations === '5' ? 'selected' : ''; ?>>5+ Donations</option>
                        <option value="10" <?php echo $filter_donations === '10' ? 'selected' : ''; ?>>10+ Donations</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                </div>
            </div>
        </form>

        <div class="alert alert-info py-2 small mb-4">
            <i class="bi bi-info-circle-fill"></i> Showing available donors compatible with <strong><?php echo htmlspecialchars($user['blood_type']); ?></strong>.
        </div>

        <div class="row g-4">
            <?php 
            $donor_count = 0;
            if (is_array($all_donors) && count($all_donors) > 0): 
                foreach($all_donors as $donor):
                    if (!empty($search_blood_type) && trim(strtoupper($donor['blood_type'])) !== trim(strtoupper($search_blood_type))) continue;
                    if (!empty($filter_donations) && $donor['total_donations'] < (int)$filter_donations) continue;
                    
                    $donor_count++;
                    $is_compatible = $donor['is_compatible'];
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm border-top border-4 border-<?php echo $is_compatible ? 'success' : 'secondary'; ?>">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($donor['full_name']); ?></h6>
                            <span class="badge bg-danger"><?php echo $donor['blood_type']; ?></span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-center text-muted small mb-2">
                                <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                                <?php echo $donor['distance_km'] > 0 ? $donor['distance_km'] . ' km away' : 'Location not set'; ?>
                            </div>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                <?php echo $donor['total_donations']; ?> donations completed
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-muted">Match Score</span>
                            <span class="fw-bold text-primary"><?php echo round($donor['priority_score']); ?>/100</span>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <?php if ($is_compatible): ?>
                                <button type="button" class="btn btn-primary btn-sm py-2" data-bs-toggle="modal" data-bs-target="#requestModal<?php echo $donor['id']; ?>">
                                    <i class="bi bi-send-fill me-1"></i> Send Request
                                </button>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary btn-sm py-2" disabled>Incompatible</button>
                            <?php endif; ?>
                            <a href="donor_profile.php?id=<?php echo $donor['id']; ?>" class="btn btn-light btn-sm py-2">View Profile</a>
                        </div>
                    </div>
                </div>
                
                <!-- Blood Request Modal (only for compatible donors) -->
                <?php if ($is_compatible): ?>
                <!-- Blood Request Modal -->
                <?php if ($is_compatible): ?>
                <div class="modal fade" id="requestModal<?php echo $donor['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg">
                            <div class="modal-header bg-primary text-white py-3">
                                <h5 class="modal-title fw-bold">Request Blood</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body p-4">
                                    <input type="hidden" name="action" value="request">
                                    <input type="hidden" name="donor_id" value="<?php echo $donor['id']; ?>">
                                    
                                    <div class="mb-4 text-center">
                                        <div class="fw-bold mb-1"><?php echo htmlspecialchars($donor['full_name']); ?></div>
                                        <div class="badge bg-danger"><?php echo $donor['blood_type']; ?></div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Amount Needed (ml)*</label>
                                        <input type="number" class="form-control" name="units_needed" min="200" max="500" value="450" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Urgency Level*</label>
                                        <select class="form-select" name="urgency" required>
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold">Medical Reason (Optional)</label>
                                        <textarea class="form-control" name="medical_reason" rows="2" placeholder="e.g. Surgery recovery..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-3">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary px-4">Send Request</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
                <?php endif; ?>
            </div>
            <?php 
                endforeach;
            else:
            ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> No donors found matching your filters.
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($donor_count === 0 && count($all_donors) > 0): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No donors matched your filter criteria. Try changing your filters.
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-3">
    <a href="recipientdashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include 'includes/footer_recipient.php'; ?>
