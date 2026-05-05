<?php
include '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $donor_id = $_SESSION['user_id'];
    
    // Verify the request exists, is approved, and belongs to this donor
    $stmt = $conn->prepare("SELECT * FROM blood_requests WHERE id = ? AND donor_id = ? AND status = 'approved'");
    $stmt->bind_param("ii", $request_id, $donor_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    
    if ($request) {
        $recipient_id = $request['recipient_id'];
        
        $conn->begin_transaction();
        try {
            // 1. Update the request status to donor_accepted
            $update_stmt = $conn->prepare("UPDATE blood_requests SET status = 'donor_accepted' WHERE id = ?");
            $update_stmt->bind_param("i", $request_id);
            $update_stmt->execute();
            
            // 2. Create an active pledge (appointment) linked to this recipient
            $reason = "Direct Request Fulfillment for Request #" . $request_id;
            $insert_stmt = $conn->prepare("INSERT INTO appointments (donor_id, recipient_id, status, reason, created_at) VALUES (?, ?, 'confirmed', ?, NOW())");
            $insert_stmt->bind_param("iis", $donor_id, $recipient_id, $reason);
            $insert_stmt->execute();
            
            $conn->commit();
            $_SESSION['message'] = "✓ Request accepted! You now have an active pledge for this recipient. When you donate, click 'Mark Complete'.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "✗ Failed to accept request: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "✗ Invalid request or it has already been processed.";
    }
}

header('Location: donordashboard.php');
exit();
?>
