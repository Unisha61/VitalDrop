<?php
include 'includes/header_donor.php';

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_pressure = sanitize_input($_POST['blood_pressure'] ?? '');
    $hemoglobin = sanitize_input($_POST['hemoglobin'] ?? '');
    $heart_rate = sanitize_input($_POST['heart_rate'] ?? '');
    $temperature = sanitize_input($_POST['temperature'] ?? '');
    $health_notes = sanitize_input($_POST['health_notes'] ?? '');
    
    // Comprehensive health info for users table
    $medical_condition = sanitize_input($_POST['medical_condition'] ?? '');
    $medications = sanitize_input($_POST['medications'] ?? '');
    $allergies = sanitize_input($_POST['allergies'] ?? '');
    
    // Validate inputs
    if (empty($blood_pressure) || empty($hemoglobin)) {
        $message = 'Blood Pressure and Hemoglobin are required!';
        $message_type = 'danger';
    } else {
        // Check if health record exists for today
        $check_today = $conn->query("SELECT id FROM health_records WHERE donor_id=$user_id AND DATE(recorded_date) = CURDATE()");
        
        if ($check_today && $check_today->num_rows > 0) {
            // Update existing record
            $record_id = $check_today->fetch_assoc()['id'];
            $update_sql = "UPDATE health_records 
                          SET blood_pressure='" . escape_db_input($blood_pressure) . "',
                              hemoglobin='" . escape_db_input($hemoglobin) . "',
                              heart_rate='" . escape_db_input($heart_rate) . "',
                              temperature='" . escape_db_input($temperature) . "',
                              health_notes='" . escape_db_input($health_notes) . "'
                          WHERE id=$record_id";
            $is_update = true;
        } else {
            // Insert new record
            $insert_sql = "INSERT INTO health_records (donor_id, blood_pressure, hemoglobin, heart_rate, temperature, health_notes, recorded_date)
                          VALUES ($user_id, 
                                  '" . escape_db_input($blood_pressure) . "',
                                  '" . escape_db_input($hemoglobin) . "',
                                  '" . escape_db_input($heart_rate) . "',
                                  '" . escape_db_input($temperature) . "',
                                  '" . escape_db_input($health_notes) . "',
                                  NOW())";
            $update_sql = $insert_sql;
            $is_update = false;
        }
        
        if ($conn->query($update_sql)) {
            // Update the users table with comprehensive health info
            $update_user_sql = "UPDATE users SET 
                                medical_condition = '" . escape_db_input($medical_condition) . "',
                                medications = '" . escape_db_input($medications) . "',
                                allergies = '" . escape_db_input($allergies) . "'
                                WHERE id = $user_id";
            $conn->query($update_user_sql);

            // Refresh user data to reflect immediately
            $user = $conn->query("SELECT * FROM users WHERE id=$user_id")->fetch_assoc();

            $message = ($is_update ? 'Health record updated' : 'Health record saved') . ' successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error saving health record!';
            $message_type = 'danger';
        }
    }
}

// Get latest health record
$latest_health = $conn->query("SELECT * FROM health_records WHERE donor_id=$user_id ORDER BY recorded_date DESC LIMIT 1");
$health_data = ($latest_health && $latest_health->num_rows > 0) ? $latest_health->fetch_assoc() : null;

// Get health history (last 10 records)
$health_history = $conn->query("SELECT * FROM health_records WHERE donor_id=$user_id ORDER BY recorded_date DESC LIMIT 10");

// Calculate donation eligibility based on comprehensive health logic
require_once '../filtering_matching.php';

$is_health_eligible = true;
$eligibility_issues = [];

if (!$health_data) {
    $is_health_eligible = false;
    $eligibility_issues[] = "No health check recorded yet.";
} else {
    // We only need to check health eligibility here, not interval, but getDonorEligibility does both.
    // So we pass null for $last_donation_date to only get health-related issues.
    $eligibility = getDonorEligibility($user, $health_data, null);
    
    if ($eligibility['status'] !== 'eligible') {
        $is_health_eligible = false;
        // Filter out the '56-day' interval message if it somehow appears
        foreach ($eligibility['messages'] as $msg) {
            if (strpos($msg, '56-day') === false) {
                $eligibility_issues[] = $msg;
            }
        }
    }
}
?><div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-heart-pulse-fill"></i> Health Record</h2>
        <p class="text-muted small mb-0">Track and update your vital health information.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Main Form -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-heart-pulse"></i> Health Check-in</h5>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> py-2 small">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Blood Pressure*</label>
                            <input type="text" class="form-control" name="blood_pressure" placeholder="e.g., 120/80" value="<?php echo $health_data['blood_pressure'] ?? ''; ?>" required>
                            <small class="text-muted d-block mt-1 small">systolic/diastolic</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hemoglobin (g/dL)*</label>
                            <input type="number" class="form-control" name="hemoglobin" min="10" max="18" step="0.1" placeholder="e.g., 14.5" value="<?php echo $health_data['hemoglobin'] ?? ''; ?>" required>
                            <small class="text-muted d-block mt-1 small">Normal: 12.5-18 g/dL</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Heart Rate (bpm)</label>
                            <input type="number" class="form-control" name="heart_rate" min="40" max="150" placeholder="e.g., 72" value="<?php echo $health_data['heart_rate'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Temperature (°C)</label>
                            <input type="number" class="form-control" name="temperature" min="35" max="40" step="0.1" placeholder="e.g., 37.0" value="<?php echo $health_data['temperature'] ?? ''; ?>">
                        </div>
                        <div class="col-12 mt-4 pt-3 border-top">
                            <h6 class="fw-bold mb-3"><i class="bi bi-file-medical"></i> Comprehensive Medical History</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Medical Conditions</label>
                            <input type="text" class="form-control" name="medical_condition" placeholder="e.g., None, Pregnant, Menstruation (Heavy/Light)..." value="<?php echo htmlspecialchars($user['medical_condition'] ?? ''); ?>">
                            <small class="text-muted d-block mt-1 small" style="font-size: 0.75rem;">Include pregnancy or menstruation details.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Current Medications</label>
                            <input type="text" class="form-control" name="medications" placeholder="e.g., None, Antibiotics, Antihistamines..." value="<?php echo htmlspecialchars($user['medications'] ?? ''); ?>">
                            <small class="text-muted d-block mt-1 small" style="font-size: 0.75rem;">Heavy meds/antibiotics may restrict donation.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Allergies</label>
                            <input type="text" class="form-control" name="allergies" placeholder="e.g., None, Dust, Pollen, Severe (Anaphylaxis)..." value="<?php echo htmlspecialchars($user['allergies'] ?? ''); ?>">
                            <small class="text-muted d-block mt-1 small" style="font-size: 0.75rem;">Note if allergies are severe.</small>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <label class="form-label small fw-bold">General Health Notes</label>
                            <textarea class="form-control" name="health_notes" rows="2" placeholder="Any other symptoms or notes..."><?php echo $health_data['health_notes'] ?? ''; ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-4"><i class="bi bi-check-circle"></i> Record Measurement</button>
                </form>
            </div>
        </div>
        
        <!-- Eligibility Status -->
        <?php if ($health_data): ?>
        <div class="alert alert-<?php echo $is_health_eligible ? 'success' : 'danger'; ?> mb-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-shield-check"></i> <?php echo $is_health_eligible ? 'Health Status Good' : 'Health Issues Detected'; ?></h6>
            <?php if ($is_health_eligible): ?>
                <p class="mb-0 small">Your health measurements look good for donation!</p>
            <?php else: ?>
                <ul class="mb-0 ps-3 small">
                    <?php foreach ($eligibility_issues as $issue): ?>
                    <li><?php echo htmlspecialchars($issue); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Health History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history"></i> Recent Health History</h6>
            </div>
            <div class="table-container mb-0">
                <?php if ($health_history && $health_history->num_rows > 0): ?>
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>BP</th>
                            <th>Hemoglobin</th>
                            <th>HR</th>
                            <th>Temp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($record = $health_history->fetch_assoc()): ?>
                        <tr>
                            <td class="fw-bold"><?php echo date('M d, Y', strtotime($record['recorded_date'])); ?></td>
                            <td><?php echo htmlspecialchars($record['blood_pressure']); ?></td>
                            <td><strong><?php echo $record['hemoglobin']; ?></strong></td>
                            <td><?php echo $record['heart_rate'] ?? '—'; ?></td>
                            <td><?php echo $record['temperature'] ?? '—'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="p-4 text-center text-muted small">No health records yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Info Panels -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle"></i> Reference Ranges</h6>
                <div class="mb-3">
                    <div class="fw-bold small">Blood Pressure</div>
                    <div class="text-muted small">Target: < 140/90</div>
                </div>
                <div class="mb-3">
                    <div class="fw-bold small">Hemoglobin</div>
                    <div class="text-muted small">Min: 12.5 (F) / 13.5 (M)</div>
                </div>
                <div class="mb-3">
                    <div class="fw-bold small">Heart Rate</div>
                    <div class="text-muted small">Normal: 60-100 bpm</div>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold text-success mb-3"><i class="bi bi-lightbulb"></i> Health Tips</h6>
                <ul class="text-muted small mb-0 ps-3">
                    <li>Drink plenty of water</li>
                    <li>Eat iron-rich foods (spinach, beans)</li>
                    <li>Avoid fatty foods before donating</li>
                    <li>Get a good night's sleep</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="back-link">
    <a href="donordashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
</div>

<?php include 'includes/footer_donor.php'; ?>

