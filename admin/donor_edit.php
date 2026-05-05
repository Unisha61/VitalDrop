<?php
include 'includes/header_admin.php';

$donor_id = sanitize_input($_GET['id'] ?? '');
if (empty($donor_id)) {
    header("Location: donor_manage.php");
    exit();
}

$donor = $conn->query("SELECT * FROM users WHERE id=$donor_id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $blood_type = sanitize_input($_POST['blood_type'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    
    $update_sql = "UPDATE users SET full_name='" . escape_db_input($full_name) . "', 
                   blood_type='" . escape_db_input($blood_type) . "', 
                   phone='" . escape_db_input($phone) . "' 
                   WHERE id=$donor_id";
    
    if ($conn->query($update_sql)) {
        echo '<div class="alert alert-success">Donor updated successfully!</div>';
        $donor = $conn->query("SELECT * FROM users WHERE id=$donor_id")->fetch_assoc();
    }
}
?>

<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-person-gear"></i> Edit Donor</h2>
        <p class="text-muted small mb-0">Update information for donor: <strong><?php echo htmlspecialchars($donor['full_name']); ?></strong></p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary">Donor Information</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($donor['full_name']); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">Email (read-only)</label>
                    <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($donor['email']); ?>" disabled>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">Blood Type</label>
                    <select class="form-select" name="blood_type">
                        <option value="">Select Blood Type</option>
                        <?php 
                        $types = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
                        foreach($types as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($donor['blood_type'] === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">Phone Number</label>
                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($donor['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Update Donor</button>
                <a href="donor_manage.php" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer_admin.php'; ?>
