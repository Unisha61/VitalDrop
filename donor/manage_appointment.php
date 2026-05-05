<?php
include 'includes/header_donor.php';

$user_id = $_SESSION['user_id'];
$apt_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';

// Get appointment
$apt = $conn->query("SELECT * FROM appointments WHERE id=$apt_id AND donor_id=$user_id");
if (!$apt || $apt->num_rows === 0) {
    header('Location: appointment.php');
    exit;
}
$appointment = $apt->fetch_assoc();

// Handle reschedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reschedule') {
        $new_date = sanitize_input($_POST['new_date'] ?? '');
        $new_time = sanitize_input($_POST['new_time'] ?? '');
        
        if (strtotime($new_date) < strtotime(date('Y-m-d'))) {
            $message = '✗ Please select a future date!';
            $message_type = 'danger';
        } else {
            $update_sql = "UPDATE appointments 
                          SET appointment_date='" . escape_db_input($new_date) . "',
                              appointment_time='" . escape_db_input($new_time) . "',
                              status='pending'
                          WHERE id=$apt_id";
            
            if ($conn->query($update_sql)) {
                $message = '✓ Appointment rescheduled successfully!';
                $message_type = 'success';
                $appointment = $conn->query("SELECT * FROM appointments WHERE id=$apt_id")->fetch_assoc();
            }
        }
    } 
    elseif ($_POST['action'] === 'cancel') {
        $cancel_reason = sanitize_input($_POST['cancel_reason'] ?? '');
        $update_sql = "UPDATE appointments 
                      SET status='cancelled',
                          cancel_reason='" . escape_db_input($cancel_reason) . "'
                      WHERE id=$apt_id";
        
        if ($conn->query($update_sql)) {
            $message = '✓ Appointment cancelled successfully!';
            $message_type = 'success';
            $appointment = $conn->query("SELECT * FROM appointments WHERE id=$apt_id")->fetch_assoc();
        }
    }
}

$can_modify = in_array($appointment['status'], ['pending', 'confirmed']) && strtotime($appointment['appointment_date']) > time();
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">📅 Manage Appointment</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Current Appointment Details -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Current Appointment Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p class="text-muted small">Date</p>
                                <h5><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small">Time</p>
                                <h5><?php echo $appointment['appointment_time']; ?></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small">Status</p>
                                <span class="badge bg-<?php echo match($appointment['status']) {
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'completed' => 'info',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                }; ?> fs-6">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($appointment['reason'])): ?>
                        <hr>
                        <p class="text-muted">
                            <strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($can_modify): ?>
                    <!-- Reschedule Form -->
                    <div class="card border-warning mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">📝 Reschedule Appointment</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="reschedule">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">New Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="new_date" 
                                               min="<?php echo date('Y-m-d'); ?>"
                                               value="<?php echo $appointment['appointment_date']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">New Time <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" name="new_time" 
                                               value="<?php echo $appointment['appointment_time']; ?>" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm">
                                    <i class="bi bi-arrow-repeat"></i> Reschedule
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Cancel Form -->
                    <div class="card border-danger">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">🚫 Cancel Appointment</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                <input type="hidden" name="action" value="cancel">
                                <div class="mb-3">
                                    <label class="form-label">Reason for Cancellation (Optional)</label>
                                    <textarea class="form-control" name="cancel_reason" rows="2" 
                                              placeholder="Tell us why you're cancelling..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Cancel Appointment
                                </button>
                            </form>
                        </div>
                    </div>
                <?php elseif ($appointment['status'] === 'cancelled'): ?>
                    <div class="alert alert-danger">
                        <strong>This appointment has been cancelled.</strong>
                        <?php if (!empty($appointment['cancel_reason'])): ?>
                        <p class="mb-0 mt-2">Reason: <?php echo htmlspecialchars($appointment['cancel_reason']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <strong>This appointment cannot be modified.</strong>
                        <?php if ($appointment['status'] === 'completed'): ?>
                        <p class="mb-0">This donation has already been completed.</p>
                        <?php else: ?>
                        <p class="mb-0">You can only modify pending or confirmed appointments that are in the future.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Info Panel -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">ℹ️ Important Notes</h6>
            </div>
            <div class="card-body small">
                <ul class="list-unstyled">
                    <li>✓ You can reschedule anytime before the appointment</li>
                    <li>✓ Cancellations help us plan better</li>
                    <li>✓ Admin approval takes 24-48 hours</li>
                    <li>✓ Completed donations cannot be modified</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="appointment.php" class="btn btn-secondary">← Back to Appointments</a>
</div>

<?php include 'includes/footer_donor.php'; ?>
