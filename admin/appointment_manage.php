<?php
include 'includes/header_admin.php';
require_once '../filtering_matching.php';

// Handle appointment approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');
    $appointment_id = (int)($_POST['appointment_id'] ?? 0);
    $rejection_reason = sanitize_input($_POST['rejection_reason'] ?? '');
    
    if ($appointment_id > 0) {
        if ($action === 'approve') {
            $update_sql = "UPDATE appointments SET status = 'confirmed' WHERE id = $appointment_id AND status = 'pending'";
            if ($conn->query($update_sql)) {
                $message = '✓ Appointment approved successfully!';
                $message_type = 'success';
            } else {
                $message = '✗ Error approving appointment';
                $message_type = 'danger';
            }
        } elseif ($action === 'reject') {
            $rejection_reason = $conn->real_escape_string($rejection_reason);
            $update_sql = "UPDATE appointments SET status = 'cancelled', cancel_reason = '$rejection_reason' WHERE id = $appointment_id AND status = 'pending'";
            if ($conn->query($update_sql)) {
                $message = '✓ Appointment rejected successfully!';
                $message_type = 'success';
            } else {
                $message = '✗ Error rejecting appointment';
                $message_type = 'danger';
            }
        }
    }
}

$status_filter = sanitize_input($_GET['status'] ?? '');
$date_from = sanitize_input($_GET['date_from'] ?? '');
$date_to = sanitize_input($_GET['date_to'] ?? '');

// Build appointment query
$sql = "SELECT a.*, d.full_name as donor_name, d.blood_type as donor_blood_type, r.full_name as recipient_name
        FROM appointments a
        LEFT JOIN users d ON a.donor_id = d.id
        LEFT JOIN users r ON a.recipient_id = r.id
        WHERE 1=1";

if (!empty($status_filter)) {
    $status_filter = $conn->real_escape_string($status_filter);
    $sql .= " AND a.status = '$status_filter'";
}
if (!empty($date_from)) {
    $date_from = $conn->real_escape_string($date_from);
    $sql .= " AND a.appointment_date >= '$date_from'";
}
if (!empty($date_to)) {
    $date_to = $conn->real_escape_string($date_to);
    $sql .= " AND a.appointment_date <= '$date_to'";
}

$sql .= " ORDER BY a.appointment_date DESC";
$result = $conn->query($sql);

// Count by status
$pending_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='pending'")->fetch_assoc()['total'] ?? 0;
$confirmed_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='confirmed'")->fetch_assoc()['total'] ?? 0;
$completed_count = $conn->query("SELECT COUNT(*) as total FROM appointments WHERE status='completed'")->fetch_assoc()['total'] ?? 0;
?>



<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-calendar-check-fill"></i> Appointment Management</h2>
        <p class="text-muted small mb-0">Track and manage donor-recipient appointments.</p>
    </div>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> py-2 small mb-4">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon text-warning"><i class="bi bi-clock"></i></div>
        <div class="stat-label">Pending</div>
        <div class="stat-value"><?php echo $pending_count; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-check2-circle"></i></div>
        <div class="stat-label">Confirmed</div>
        <div class="stat-value"><?php echo $confirmed_count; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-label">Completed</div>
        <div class="stat-value"><?php echo $completed_count; ?></div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" id="statusFilter">
                    <option value="">Status (All)</option>
                    <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo ($status_filter === 'confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="completed" <?php echo ($status_filter === 'completed') ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateFrom" value="<?php echo $date_from; ?>">
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateTo" value="<?php echo $date_to; ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" onclick="applyFilters()"><i class="bi bi-search"></i></button>
                <a href="appointment_manage.php" class="btn btn-outline-secondary px-3"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php if ($result->num_rows === 0): ?>
    <div class="col-12">
        <div class="card bg-light border-0 py-5 text-center">
            <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
            <p class="text-muted">No appointments matching criteria.</p>
        </div>
    </div>
    <?php else: ?>
        <?php while($row = $result->fetch_assoc()): 
            $status_class = match($row['status']) {
                'pending' => 'warning',
                'confirmed' => 'primary',
                'completed' => 'success',
                'cancelled' => 'danger',
                default => 'secondary'
            };
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                        <div class="small text-muted fw-bold">#<?php echo $row['id']; ?></div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="fw-bold"><?php echo htmlspecialchars($row['donor_name']); ?></div>
                        <div class="text-muted small mb-2"><i class="bi bi-arrow-down-short"></i> To: <?php echo htmlspecialchars($row['recipient_name']); ?></div>
                        <div class="small fw-bold"><i class="bi bi-calendar-check me-2"></i><?php echo date('M d, Y', strtotime($row['appointment_date'])); ?></div>
                        <div class="small"><i class="bi bi-clock me-2"></i><?php echo $row['appointment_time']; ?></div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <?php if ($row['status'] === 'pending'): ?>
                            <form method="POST" class="flex-grow-1">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Approve</button>
                            </form>
                            <button type="button" class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#rejectModal" onclick="document.getElementById('rejectAppointmentId').value=<?php echo $row['id']; ?>"><i class="bi bi-x-lg"></i></button>
                        <?php endif; ?>
                        <a href="appupdate.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm <?php echo $row['status'] === 'pending' ? '' : 'flex-grow-1'; ?> border">Edit</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    let url = '?';
    if (status) url += 'status=' + encodeURIComponent(status) + '&';
    if (dateFrom) url += 'date_from=' + encodeURIComponent(dateFrom) + '&';
    if (dateTo) url += 'date_to=' + encodeURIComponent(dateTo);
    
    window.location = url;
}

function setRejectId(appointmentId) {
    document.getElementById('rejectAppointmentId').value = appointmentId;
    document.getElementById('rejectionReason').value = '';
}

document.getElementById('statusFilter').addEventListener('change', applyFilters);
document.getElementById('dateFrom').addEventListener('change', applyFilters);
document.getElementById('dateTo').addEventListener('change', applyFilters);
</script>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold">Reject Appointment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="appointment_id" id="rejectAppointmentId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Rejection</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Enter rejection reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4">Confirm Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer_admin.php'; ?>
