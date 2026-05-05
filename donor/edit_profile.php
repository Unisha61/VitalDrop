<?php
include 'includes/header_donor.php';

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
    $weight = sanitize_input($_POST['weight'] ?? '');
    $medical_condition = sanitize_input($_POST['medical_condition'] ?? '');
    $medications = sanitize_input($_POST['medications'] ?? '');
    $allergies = sanitize_input($_POST['allergies'] ?? '');
    
    // Validate weight
    if (!empty($weight) && (!is_numeric($weight) || $weight < 50)) {
        $message = 'Weight must be at least 50 kg!';
        $message_type = 'danger';
    } else {
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
                       weight=" . (empty($weight) ? "NULL" : $weight) . ",
                       medical_condition='" . escape_db_input($medical_condition) . "',
                       medications='" . escape_db_input($medications) . "',
                       allergies='" . escape_db_input($allergies) . "'
                       WHERE id=$user_id";
        
        if ($conn->query($update_sql)) {
            $message = 'Profile updated successfully!';
            $message_type = 'success';
            $user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
        } else {
            $message = 'Error updating profile!';
            $message_type = 'danger';
        }
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
        <p class="text-muted small mb-0">Update your account details and medical information.</p>
    </div>
</div>

<div class="row">
    <!-- Main Form -->
<div class="row g-4">
    <!-- Main Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear"></i> Edit Your Profile</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> py-2 small">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
            
            <form method="POST">
                <!-- Personal Information -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person"></i> Personal Information</h6>
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
                                <option value="">Select</option>
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
                            <label class="form-label small fw-bold">Weight (kg)</label>
                            <input type="number" class="form-control" name="weight" min="50" step="0.1" value="<?php echo $user['weight'] ?? ''; ?>">
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
                
                <!-- Blood Type -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-droplet"></i> Blood Information</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Blood Type*</label>
                        <select class="form-select" name="blood_type" required>
                            <option value="">Select</option>
                            <?php foreach(['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'] as $type): ?>
                                <option value="<?php echo $type; ?>" <?php echo ($user['blood_type'] === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Health Information -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-heart-pulse"></i> Health Information</h6>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Medical Conditions</label>
                        <textarea class="form-control" name="medical_condition" rows="2" placeholder="e.g., Diabetes, High Blood Pressure"><?php echo htmlspecialchars($user['medical_condition'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Current Medications</label>
                        <textarea class="form-control" name="medications" rows="2" placeholder="List medications you're taking"><?php echo htmlspecialchars($user['medications'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Allergies</label>
                        <textarea class="form-control" name="allergies" rows="2" placeholder="e.g., Latex, Penicillin"><?php echo htmlspecialchars($user['allergies'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Profile</button>
                    <a href="donordashboard.php" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Info Panels -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-primary"><i class="bi bi-info-circle"></i> Why this matters?</h6>
                <p class="text-muted small mb-0">Your profile details help us match you with recipients and ensure your donation safety.</p>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold text-danger"><i class="bi bi-shield-check"></i> Donation Requirements</h6>
                <ul class="text-muted small mb-0 ps-3">
                    <li>Age: 18-65 years</li>
                    <li>Weight: Min 50 kg</li>
                    <li>Interval: 56 days between donations</li>
                    <li>Hemoglobin: Healthy levels required</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_donor.php'; ?>

