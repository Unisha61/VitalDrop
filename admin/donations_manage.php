<?php include 'includes/header_admin.php';
require_once '../filtering_matching.php';
require_once '../connect.php';

// Debug: Check if POST is received
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST REQUEST RECEIVED: " . json_encode($_POST));
}

$message = '';
$message_type = '';

// Handle appointment status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_appointment_status') {
        $appointment_id = (int)($_POST['appointment_id'] ?? 0);
        $new_status = $conn->real_escape_string(trim($_POST['status'] ?? ''));

        if ($appointment_id > 0 && !empty($new_status)) {
            // Map user-friendly names to database ENUM values
            $status_map = [
                'approve' => 'confirmed',
                'approved' => 'confirmed',
                'confirmed' => 'confirmed',
                'reject' => 'cancelled',
                'rejected' => 'cancelled',
                'cancelled' => 'cancelled'
            ];
            
            $db_status = $status_map[strtolower($new_status)] ?? $new_status;
            $update_query = "UPDATE appointments SET status = '$db_status' WHERE id = $appointment_id";
            
            if ($conn->query($update_query)) {
                // Verify the update was successful
                $verify_query = "SELECT status FROM appointments WHERE id = $appointment_id";
                $verify_result = $conn->query($verify_query);
                $verify_row = $verify_result->fetch_assoc();
                $actual_status = $verify_row['status'] ?? 'UNKNOWN';
                
                $display_status = ucfirst($actual_status);
                $message = '✓ Appointment ID #' . $appointment_id . ' status updated to ' . $display_status . '! (Verified: ' . $actual_status . ')';
                $message_type = 'success';
            } else {
                $message = '✗ Database error: ' . $conn->error;
                $message_type = 'error';
            }
        } else {
            $message = '✗ Invalid appointment ID or status (ID: ' . $appointment_id . ', Status: ' . $new_status . ')';
            $message_type = 'error';
        }
    }
}



// Added logic to update blood inventory when an appointment is marked as completed.
// ENHANCED: Now also creates donation record and updates donor statistics
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_appointment') {
    $appointment_id = (int)$_POST['appointment_id'];

    // Fetch appointment details
    $appointment = $conn->query("SELECT a.*, u.blood_type FROM appointments a JOIN users u ON a.donor_id = u.id WHERE a.id = $appointment_id")->fetch_assoc();
    
    if (!$appointment) {
        $message = '✗ Appointment not found';
        $message_type = 'danger';
    } else {
        $donor_id = $appointment['donor_id'];
        $donated_units = 450; // FIXED: Standard donation amount (450 ml)
        $blood_type = $appointment['blood_type']; // FIXED: Get from users table via JOIN
        $appointment_date = $appointment['appointment_date'];

        // Begin transaction
        $conn->begin_transaction();

        try {
            // 1. Mark appointment as completed
            $conn->query("UPDATE appointments SET status = 'completed' WHERE id = $appointment_id");

            // 2. Create/Insert donation record in donations table
            // This updates donor statistics (total donations, units donated)
            $today = date('Y-m-d'); // Use today's date for when blood was actually collected
            $insert_donation = "INSERT INTO donations (donor_id, blood_type, units, donation_date, status) 
                 VALUES ($donor_id, '$blood_type', $donated_units, '$today', 'completed')";
            $conn->query($insert_donation);

            // 3. Update blood inventory
            $conn->query("UPDATE blood_inventory SET quantity = quantity + $donated_units WHERE blood_type = '$blood_type'");

            // Commit transaction
            $conn->commit();
            $message = '✓ Appointment completed! Donor statistics updated. Blood inventory updated!';
            $message_type = 'success';
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $message = '✗ Failed to complete appointment: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// IMPROVED: Enhanced donation and appointment statistics for admin dashboard
$donation_stats = [
    'completed_donations' => $conn->query("SELECT COUNT(*) FROM donations WHERE status='completed'")->fetch_row()[0],
    'total_units_collected' => $conn->query("SELECT COALESCE(SUM(units), 0) FROM donations WHERE status='completed'")->fetch_row()[0],
    'pending_appointments' => $conn->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetch_row()[0],
    'total_blood_units' => $conn->query("SELECT COALESCE(SUM(quantity), 0) FROM blood_inventory")->fetch_row()[0]
];

// Fetch donations and appointments
$donations_sql = "SELECT d.id, u.full_name, d.blood_type, d.units, d.donation_date, d.status FROM donations d JOIN users u ON d.donor_id = u.id ORDER BY d.donation_date DESC";
$appointments_sql = "SELECT a.id, u.full_name, u.email, a.appointment_date, a.status FROM appointments a JOIN users u ON a.donor_id = u.id ORDER BY a.appointment_date DESC";

$donations_result = $conn->query($donations_sql);
$appointments_result = $conn->query($appointments_sql);

?>

<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-droplet-fill"></i> Donations & Appointments</h2>
        <p class="text-muted small mb-0">Monitor collection statistics and manage scheduling.</p>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo ($message_type === 'success') ? 'success' : 'danger'; ?> py-2 small mb-4">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="bi bi-check-circle"></i></div>
        <div class="stat-label">Completed Donations</div>
        <div class="stat-value"><?php echo $donation_stats['completed_donations']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-droplet-fill"></i></div>
        <div class="stat-label">Total Volume</div>
        <div class="stat-value"><?php echo $donation_stats['total_units_collected']; ?> <small class="fs-6">ml</small></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-warning"><i class="bi bi-calendar-event"></i></div>
        <div class="stat-label">Pending Apps</div>
        <div class="stat-value"><?php echo $donation_stats['pending_appointments']; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-box-seam"></i></div>
        <div class="stat-label">Blood in Stock</div>
        <div class="stat-value"><?php echo $donation_stats['total_blood_units']; ?> <small class="fs-6">ml</small></div>
    </div>
</div>










<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar3"></i> Recent Appointments</h6>
        <small class="text-muted">Updated: <?php echo date('H:i'); ?></small>
    </div>
    <div class="table-container mb-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Donor</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $fresh_appointments_sql = "SELECT a.id, u.full_name, u.email, a.appointment_date, a.status FROM appointments a JOIN users u ON a.donor_id = u.id ORDER BY a.id DESC";
                $fresh_appointments_result = $conn->query($fresh_appointments_sql);
                
                if ($fresh_appointments_result && $fresh_appointments_result->num_rows > 0):
                    $sn = 1;
                    while ($row = $fresh_appointments_result->fetch_assoc()): 
                        $status = !empty($row['status']) ? $row['status'] : 'pending';
                        $status_class = match($status) {
                            'pending' => 'warning',
                            'confirmed' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'secondary'
                        };
                ?>
                    <tr>
                        <td><div class="fw-bold"><?php echo $sn++; ?></div></td>
                        <td><div class="fw-bold"><?php echo htmlspecialchars($row['full_name']); ?></div></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                        <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span></td>
                        <td class="text-end">
                            <?php if ($status === 'pending'): ?>
                                <div class="btn-group">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_appointment_status">
                                        <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check"></i></button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="update_appointment_status">
                                        <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x"></i></button>
                                    </form>
                                </div>
                            <?php elseif ($status === 'confirmed'): ?>
                                <form method="POST">
                                    <input type="hidden" name="action" value="complete_appointment">
                                    <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-success px-3">Mark Complete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No appointments found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
include 'includes/footer_admin.php'; ?>
