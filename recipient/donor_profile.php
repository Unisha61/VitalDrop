<?php
include 'includes/header_recipient.php';

$donor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($donor_id === 0) {
    header('Location: matched_donors.php');
    exit;
}

// Get donor profile
$donor = $conn->query("SELECT * FROM users WHERE id=$donor_id AND role='donor'")->fetch_assoc();
if (!$donor) {
    header('Location: matched_donors.php');
    exit;
}

// Get donor statistics
$donations = $conn->query("SELECT COUNT(*) as count FROM donations WHERE donor_id=$donor_id AND status='completed'");
$donation_count = $donations->fetch_assoc()['count'] ?? 0;

$units = $conn->query("SELECT SUM(units) as total FROM donations WHERE donor_id=$donor_id AND status='completed'");
$total_units = $units->fetch_assoc()['total'] ?? 0;

// Check if recipient already sent a request to this donor
$existing_request = $conn->query("SELECT id FROM blood_requests WHERE donor_id=$donor_id AND recipient_id=" . $_SESSION['user_id'] . " AND status='pending'");
$has_pending_request = ($existing_request && $existing_request->num_rows > 0);
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Donor Profile Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div style="font-size: 4rem; margin-bottom: 10px;">🩸</div>
                        <h5><?php echo htmlspecialchars($donor['full_name']); ?></h5>
                        <span class="badge bg-danger fs-5"><?php echo $donor['blood_type']; ?></span>
                    </div>
                    <div class="col-md-9">
                        <h6 class="text-secondary">Contact Information</h6>
                        <p class="mb-1">
                            <strong>Email:</strong> 
                            <a href="mailto:<?php echo htmlspecialchars($donor['email']); ?>">
                                <?php echo htmlspecialchars($donor['email']); ?>
                            </a>
                        </p>
                        <p class="mb-1">
                            <strong>Phone:</strong> 
                            <?php echo !empty($donor['phone']) ? htmlspecialchars($donor['phone']) : '<em class="text-muted">Not provided</em>'; ?>
                        </p>
                        <p class="mb-1">
                            <strong>Location:</strong> 
                            <?php echo !empty($donor['address']) ? htmlspecialchars(substr($donor['address'], 0, 50)) : '<em class="text-muted">Not provided</em>'; ?>
                        </p>
                        
                        <hr>
                        
                        <h6 class="text-secondary">Donation Record</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Total Donations</small>
                                    <h5 class="mb-0 text-primary"><?php echo $donation_count; ?></h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">Total Blood Donated</small>
                                    <h5 class="mb-0 text-success"><?php echo $total_units; ?> units</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Donor Bio -->
        <?php if (!empty($donor['address'])): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">ℹ️ Donor Information</h6>
            </div>
            <div class="card-body">
                <p><strong>Address:</strong> <?php echo htmlspecialchars($donor['address']); ?></p>
                <p class="mb-0"><strong>Member Since:</strong> <?php echo date('M d, Y', strtotime($donor['created_at'] ?? '')); ?></p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Compatibility Info -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">🩸 Blood Compatibility</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">
                    ✓ This donor has <strong><?php echo htmlspecialchars($donor['blood_type']); ?></strong> blood type
                </p>
                <p class="text-muted small mt-2">
                    This blood type is compatible with your blood requirements.
                </p>
            </div>
        </div>
    </div>
    
    <!-- Action Panel -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">💚 Request Blood</h6>
            </div>
            <div class="card-body">
                <?php if ($has_pending_request): ?>
                    <div class="alert alert-warning">
                        You already have a pending blood request with this donor.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h6 class="mb-0">📊 Donor Stats</h6>
            </div>
            <div class="card-body small">
                <p class="mb-2"><strong>Experience Level:</strong></p>
                <div class="mb-3">
                    <?php 
                    if ($donation_count >= 50) {
                        echo '<span class="badge bg-danger fs-6">Platinum Donor</span>';
                    } elseif ($donation_count >= 25) {
                        echo '<span class="badge bg-warning fs-6">Gold Donor</span>';
                    } elseif ($donation_count >= 10) {
                        echo '<span class="badge bg-info fs-6">Regular Donor</span>';
                    } elseif ($donation_count >= 5) {
                        echo '<span class="badge bg-success fs-6">Active Donor</span>';
                    } else {
                        echo '<span class="badge bg-secondary fs-6">New Donor</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
    </div>
</div>
