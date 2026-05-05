<?php
include 'includes/header_admin.php';

$search = sanitize_input($_GET['search'] ?? '');

$sql = "SELECT id, full_name, email, phone, created_at, blood_type, is_active FROM users WHERE role='recipient'";
if (!empty($search)) {
    $sql .= " AND (full_name LIKE '%" . escape_db_input($search) . "%' OR email LIKE '%" . escape_db_input($search) . "%')";
}
$sql .= " ORDER BY full_name";

$result = $conn->query($sql);
$total_recipients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role='recipient'")->fetch_assoc()['count'];
$filtered_count = $result->num_rows;
?>



<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-heart-pulse-fill"></i> Manage Recipients</h2>
        <p class="text-muted small mb-0">Total registered recipients: <?php echo $total_recipients; ?></p>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-person-heart"></i></div>
        <div class="stat-label">Total Recipients</div>
        <div class="stat-value"><?php echo $total_recipients; ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-search"></i></div>
        <div class="stat-label">Search Result</div>
        <div class="stat-value"><?php echo $filtered_count; ?></div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET">
            <div class="row g-3 align-items-center">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search"></i> Search</button>
                    <a href="recipient_manage.php" class="btn btn-outline-secondary px-3"><i class="bi bi-x"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <?php if ($result->num_rows === 0): ?>
    <div class="col-12">
        <div class="card bg-light border-0 py-5 text-center">
            <i class="bi bi-inbox display-4 text-muted mb-3 d-block"></i>
            <p class="text-muted">No recipients found matching search.</p>
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
                        <span class="badge bg-danger"><?php echo htmlspecialchars($row['blood_type'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="small text-muted mb-4">
                        <div class="mb-1"><i class="bi bi-envelope me-2"></i><?php echo htmlspecialchars($row['email']); ?></div>
                        <div class="mb-1"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></div>
                        <div><i class="bi bi-calendar-event me-2"></i>Joined: <?php echo date('M d, Y', strtotime($row['created_at'])); ?></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="recipient_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-light btn-sm flex-grow-1 border">Edit</a>
                        <?php if(!isset($row['is_active']) || $row['is_active'] == 1): ?>
                            <a href="recipient_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-warning btn-sm px-3" onclick="return confirm('Deactivate this recipient? They will not be able to login.')"><i class="bi bi-person-dash"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer_admin.php'; ?>
