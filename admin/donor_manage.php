<?php
include 'includes/header_admin.php';
require_once '../filtering_matching.php';

$blood_type_filter = sanitize_input($_GET['blood_type'] ?? '');
$blood_types = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];

// Get all donors
$sql = "SELECT * FROM users WHERE role='donor'";
if (!empty($blood_type_filter)) {
    $blood = $conn->real_escape_string($blood_type_filter);
    $sql .= " AND blood_type = '$blood'";
}
$sql .= " ORDER BY full_name ASC";
$result = $conn->query($sql);
$donor_count = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='donor'")->fetch_assoc()['total'] ?? 0;
$filtered_count = $result->num_rows;
?>



<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-people-fill"></i> Manage Donors</h2>
        <p class="text-muted small mb-0">Total registered donors: <?php echo $donor_count; ?></p>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-people"></i></div>
        <div class="stat-label">Total Donors</div>
        <div class="stat-value"><?php echo $donor_count; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-funnel"></i></div>
        <div class="stat-label">Filtered Result</div>
        <div class="stat-value"><?php echo $filtered_count; ?></div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <select class="form-select" id="bloodTypeFilter">
                    <option value="">Filter by Blood Type (All)</option>
                    <?php foreach($blood_types as $type): ?>
                        <option value="<?php echo $type; ?>" <?php echo ($blood_type_filter === $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" onclick="applyFilter()"><i class="bi bi-search"></i> Filter</button>
                <a href="donor_manage.php" class="btn btn-outline-secondary px-3"><i class="bi bi-x"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php if ($result->num_rows === 0): ?>
    <div class="col-12">
        <div class="card bg-light border-0 py-5 text-center">
            <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
            <p class="text-muted">No donors found matching criteria.</p>
        </div>
    </div>
    <?php else: ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">
                            <?php echo htmlspecialchars($row['full_name']); ?>
                            <?php if(isset($row['is_active']) && $row['is_active'] == 0): ?>
                                <span class="badge bg-secondary ms-1" style="font-size:0.65rem;">Deactivated</span>
                            <?php endif; ?>
                        </h6>
                        <span class="badge bg-danger"><?php echo htmlspecialchars($row['blood_type']); ?></span>
                    </div>
                    <div class="small text-muted mb-4">
                        <div class="mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($row['email']); ?></div>
                        <div class="mb-1"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></div>
                        <div><i class="bi bi-calendar-event me-2"></i>Joined: <?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="donor_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm flex-grow-1 border">Edit</a>
                        <?php if(!isset($row['is_active']) || $row['is_active'] == 1): ?>
                            <a href="donor_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-warning btn-sm px-3" onclick="return confirm('Deactivate this donor? They will not be able to login.')"><i class="bi bi-person-dash"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script>
function applyFilter() {
    const bloodType = document.getElementById('bloodTypeFilter').value;
    window.location = '?blood_type=' + encodeURIComponent(bloodType);
}

document.getElementById('bloodTypeFilter').addEventListener('change', applyFilter);
</script>

<?php include 'includes/footer_admin.php'; ?>
