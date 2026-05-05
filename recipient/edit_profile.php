<?php
include 'includes/header_recipient.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $blood_type = sanitize_input($_POST['blood_type'] ?? '');
    $date_of_birth = sanitize_input($_POST['date_of_birth'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $medical_condition = sanitize_input($_POST['medical_condition'] ?? '');
    $admission_reason = sanitize_input($_POST['admission_reason'] ?? '');
    $current_medications = sanitize_input($_POST['current_medications'] ?? '');
    $allergies = sanitize_input($_POST['allergies'] ?? '');
    $emergency_contact = sanitize_input($_POST['emergency_contact'] ?? '');
    $emergency_phone = sanitize_input($_POST['emergency_phone'] ?? '');
    
    $update_sql = "UPDATE users SET 
                   full_name='" . escape_db_input($full_name) . "', 
                   phone='" . escape_db_input($phone) . "',
                   address='" . escape_db_input($address) . "',
                   city='" . escape_db_input($_POST['city'] ?? '') . "',
                   latitude=" . (empty($_POST['latitude']) ? "NULL" : escape_db_input($_POST['latitude'])) . ",
                   longitude=" . (empty($_POST['longitude']) ? "NULL" : escape_db_input($_POST['longitude'])) . ",
                   blood_type='" . escape_db_input($blood_type) . "',
                   date_of_birth='" . escape_db_input($date_of_birth) . "',
                   gender='" . escape_db_input($gender) . "',
                   medical_condition='" . escape_db_input($medical_condition) . "',
                   admission_reason='" . escape_db_input($admission_reason) . "',
                   medications='" . escape_db_input($current_medications) . "',
                   allergies='" . escape_db_input($allergies) . "',
                   emergency_contact='" . escape_db_input($emergency_contact) . "',
                   emergency_phone='" . escape_db_input($emergency_phone) . "'
                   WHERE id=$user_id";
    
    if ($conn->query($update_sql)) {
        $message = '✓ Profile updated successfully!';
        $message_type = 'success';
        $user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
    } else {
        $message = '✗ Error updating profile!';
        $message_type = 'danger';
    }
}

// Calculate age from date of birth
$age = '';
if (!empty($user['date_of_birth'])) {
    $dob = new DateTime($user['date_of_birth']);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
}
?><div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-person-gear-fill"></i> Profile Settings</h2>
        <p class="text-muted small mb-0">Update your medical profile and contact information.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Main Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-gear"></i> Edit Your Profile</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> py-2 small mb-4">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                <!-- Personal Information -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-person"></i> Personal Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name*</label>
                            <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email (Read-only)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" value="<?php echo $user['date_of_birth'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">Select Gender</option>
                                <option value="M" <?php echo ($user['gender'] === 'M') ? 'selected' : ''; ?>>Male</option>
                                <option value="F" <?php echo ($user['gender'] === 'F') ? 'selected' : ''; ?>>Female</option>
                                <option value="O" <?php echo ($user['gender'] === 'O') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone</label>
                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Blood Type Needed*</label>
                            <select class="form-select" name="blood_type" required>
                                <option value="">Select Blood Type</option>
                                <?php foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($user['blood_type'] === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address</label>
                            <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">City</label>
                            <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" placeholder="e.g., Kathmandu">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Latitude</label>
                            <input type="text" class="form-control" name="latitude" value="<?php echo htmlspecialchars($user['latitude'] ?? ''); ?>" placeholder="e.g., 27.7172">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Longitude</label>
                            <input type="text" class="form-control" name="longitude" value="<?php echo htmlspecialchars($user['longitude'] ?? ''); ?>" placeholder="e.g., 85.3240">
                        </div>
                    </div>
                </div>
                    
                <!-- Medical Information -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-heart-pulse"></i> Medical Information</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Primary Condition*</label>
                        <textarea class="form-control" name="medical_condition" rows="2" required placeholder="e.g. Anemia, Leukemia..."><?php echo htmlspecialchars($user['medical_condition'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Admission/Treatment Reason</label>
                        <textarea class="form-control" name="admission_reason" rows="2" placeholder="Why do you need transfusion?"><?php echo htmlspecialchars($user['admission_reason'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Current Medications</label>
                        <textarea class="form-control" name="current_medications" rows="2" placeholder="List your medications"><?php echo htmlspecialchars($user['medications'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Allergies</label>
                        <textarea class="form-control" name="allergies" rows="2" placeholder="e.g. Penicillin..."><?php echo htmlspecialchars($user['allergies'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <!-- Emergency Contact -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-telephone-outbound"></i> Emergency Contact</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Contact Name</label>
                            <input type="text" class="form-control" name="emergency_contact" value="<?php echo htmlspecialchars($user['emergency_contact'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Contact Phone</label>
                            <input type="tel" class="form-control" name="emergency_phone" value="<?php echo htmlspecialchars($user['emergency_phone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Profile</button>
                    <a href="recipientdashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Info Panels -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle"></i> Why this matters?</h6>
                <p class="text-muted small mb-0">Accurate medical info helps donors and medical staff ensure safe blood matching and transfusion.</p>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold text-danger mb-3"><i class="bi bi-droplet"></i> Compatibility Info</h6>
                <div class="text-muted small mb-2"><strong>Universal Donor:</strong> O-</div>
                <div class="text-muted small"><strong>Universal Recipient:</strong> AB+</div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_recipient.php'; ?>
