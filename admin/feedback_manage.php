<?php
include 'includes/header_admin.php';

$result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
$total_feedback = $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
?>



<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-chat-left-quote"></i> User Feedback</h2>
        <p class="text-muted small mb-0">Review and manage messages from users.</p>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon"><i class="bi bi-envelope-paper"></i></div>
        <div class="stat-label">Total Messages</div>
        <div class="stat-value"><?php echo $total_feedback; ?></div>
    </div>
</div>

<div class="row g-4">
    <?php 
    $feedback_result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
    if ($feedback_result->num_rows === 0): 
    ?>
    <div class="col-12">
        <div class="card bg-light border-0 py-5 text-center text-muted">
            <i class="bi bi-inbox display-4 d-block mb-3"></i>
            No feedback received yet.
        </div>
    </div>
    <?php else: ?>
        <?php while($row = $feedback_result->fetch_assoc()): ?>
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['name']); ?></div>
                        <small class="text-muted"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></small>
                    </div>
                    <div class="small text-muted mb-3">
                        <i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?>
                    </div>
                    <div class="fw-bold small mb-2 text-dark">Sub: <?php echo htmlspecialchars($row['subject']); ?></div>
                    <div class="bg-light p-3 rounded small text-muted mb-3" style="min-height: 80px;">
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                    </div>
                    <div class="text-end">
                        <a href="reviewdelete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('Delete this feedback?')">
                            <i class="bi bi-trash me-1"></i> Delete
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer_admin.php'; ?>
