<?php
include 'includes/header_admin.php';

$message = '';
$message_type = '';

// Handle add/update blood inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $blood_type = sanitize_input($_POST['blood_type'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        
        if (empty($blood_type) || $quantity <= 0) {
            $message = '✗ Blood type and quantity are required!';
            $message_type = 'danger';
        } else {
            // Check if blood type already exists
            $check = $conn->query("SELECT id FROM blood_inventory WHERE blood_type = '" . escape_db_input($blood_type) . "'");
            
            if ($check->num_rows > 0) {
                // Update existing
                $update_sql = "UPDATE blood_inventory SET quantity = quantity + $quantity WHERE blood_type = '" . escape_db_input($blood_type) . "'";
                if ($conn->query($update_sql)) {
                    $message = '✓ Blood inventory updated!';
                    $message_type = 'success';
                }
            } else {
                // Insert new
                $insert_sql = "INSERT INTO blood_inventory (blood_type, quantity) VALUES ('" . escape_db_input($blood_type) . "', $quantity)";
                if ($conn->query($insert_sql)) {
                    $message = '✓ Blood inventory added!';
                    $message_type = 'success';
                }
            }
        }
    } 
        
elseif ($action === 'update_qty') {
      $inventory_id = (int)($_POST['inventory_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        if ($inventory_id > 0 && $quantity >= 0) {
            $update_sql = "UPDATE blood_inventory SET quantity = $quantity WHERE id = $inventory_id";
            if ($conn->query($update_sql)) {
                $message = '✓ Quantity updated!';
                $message_type = 'success';
            }
        }
    }
}

// Get all blood inventory
$inventory_sql = "SELECT * FROM blood_inventory ORDER BY blood_type ASC";
$inventory_result = $conn->query($inventory_sql);

$blood_types = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
?>



<div class="dashboard-header mb-4">
    <div>
        <h2><i class="bi bi-droplet-fill"></i> Blood Inventory</h2>
        <p class="text-muted small mb-0">Manage current blood stock levels.</p>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $message_type; ?> py-2 small mb-4">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

        <!-- Statistics -->
        <?php 
        $total_units = 0;
        $blood_types_available = 0;
        if ($inventory_result && $inventory_result->num_rows > 0) {
            $inventory_result->data_seek(0);
            while ($row = $inventory_result->fetch_assoc()) {
                $total_units += $row['quantity'];
                if ($row['quantity'] > 0) $blood_types_available++;
            }
        }
        ?>
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon text-danger"><i class="bi bi-droplet"></i></div>
        <div class="stat-label">Total Volume</div>
        <div class="stat-value"><?php echo $total_units; ?> <small class="fs-6">ml</small></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="bi bi-grid-3x3-gap"></i></div>
        <div class="stat-label">Types in Stock</div>
        <div class="stat-value"><?php echo $blood_types_available; ?>/8</div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-lg"></i> Update Inventory</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Blood Type</label>
                    <select class="form-select" name="blood_type" required>
                        <option value="">Select Blood Type</option>
                        <?php foreach($blood_types as $type): ?>
                            <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-bold">Quantity to Add (ml)</label>
                    <input type="number" class="form-control" name="quantity" min="1" placeholder="e.g. 450" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Add Stock</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-archive"></i> Stock Summary</h6>
    </div>
    <div class="table-container mb-0">
        <?php if ($inventory_result && $inventory_result->num_rows > 0): ?>
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Blood Type</th>
                        <th>Available Volume</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $inventory_result->data_seek(0);
                    while ($row = $inventory_result->fetch_assoc()): 
                        $status_color = ($row['quantity'] <= 0) ? 'danger' : (($row['quantity'] < 1000) ? 'warning' : 'success');
                        $status_text = ($row['quantity'] <= 0) ? 'Out of Stock' : (($row['quantity'] < 1000) ? 'Low Stock' : 'Optimal');
                    ?>
                    <tr>
                        <td><span class="badge bg-danger fs-6"><?php echo $row['blood_type']; ?></span></td>
                        <td class="fw-bold"><?php echo $row['quantity']; ?> ml</td>
                        <td><span class="badge bg-<?php echo $status_color; ?>"><?php echo $status_text; ?></span></td>
                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($row['last_updated'])); ?></td>
                        <td class="text-end">
                        
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-droplet-x display-4 d-block mb-3"></i>
                No blood stock records found.
            </div>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>

<?php include 'includes/footer_admin.php'; ?>
        