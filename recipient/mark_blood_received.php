<?php
include '../connect.php';
require_role('recipient');

$user_id = $_SESSION['user_id'];
$request_id = (int)($_POST['request_id'] ?? 0);
$received_ml = isset($_POST['received_ml']) ? (int)$_POST['received_ml'] : 0;

// Validate received amount
if ($received_ml < 100 || $received_ml > 500) {
    $_SESSION['error_message'] = '✗ Received amount must be between 100 and 500 ml.';
    header('Location: blood_request.php');
    exit;
}

if ($request_id <= 0) {
    $_SESSION['error_message'] = 'Invalid blood request ID';
    header('Location: blood_request.php');
    exit;
}

// Get blood request details
$blood_request = $conn->query("SELECT * FROM blood_requests WHERE id=$request_id AND recipient_id=$user_id AND status='approved'")->fetch_assoc();

if (!$blood_request) {
    $_SESSION['error_message'] = 'Blood request not found or not approved for you';
    header('Location: blood_request.php');
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Update blood request status to fulfilled with actual received amount
    $update_request_query = "UPDATE blood_requests SET status='fulfilled', units_received=$received_ml WHERE id=$request_id";
    if (!$conn->query($update_request_query)) {
        throw new Exception("Failed to update blood request: " . $conn->error);
    }

    // 2. Decrement blood inventory with actual received amount
    $decrement_query = "UPDATE blood_inventory SET quantity = quantity - $received_ml 
                       WHERE blood_type='{$blood_request['blood_type']}' AND quantity >= $received_ml";
    if (!$conn->query($decrement_query)) {
        throw new Exception("Failed to update blood inventory: " . $conn->error);
    }

    // Check if inventory was actually updated (availability check)
    if ($conn->affected_rows === 0) {
        throw new Exception("Insufficient blood units available for transfusion");
    }

    // 3. Insert into transfusion history with actual received amount
    $transfusion_reason = $conn->real_escape_string($blood_request['medical_reason']);
    $insert_history = "INSERT INTO transfusion_history (recipient_id, blood_type, units_received, transfusion_date, transfusion_reason, status) 
                      VALUES ($user_id, '{$blood_request['blood_type']}', $received_ml, NOW(), '$transfusion_reason', 'completed')";
    if (!$conn->query($insert_history)) {
        throw new Exception("Failed to record transfusion history: " . $conn->error);
    }

    // Commit transaction
    $conn->commit();

    $_SESSION['success_message'] = "✓ Blood received successfully! You received {$received_ml} ml. Inventory has been updated.";
    header('Location: blood_request.php');
    exit;

} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $_SESSION['error_message'] = '✗ Error: ' . $e->getMessage();
    header('Location: blood_request.php');
    exit;
}
?>
