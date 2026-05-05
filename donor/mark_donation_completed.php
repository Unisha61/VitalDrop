<?php
include '../connect.php'; // Database connection
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = (int)$_POST['appointment_id'];
    $donor_id = $_SESSION['user_id']; // Assuming donor is logged in
    
    // Get donation amount in ml from POST, with validation
    $donated_ml = isset($_POST['donated_ml']) ? (int)$_POST['donated_ml'] : 450;
    
    // Validate ml amount is within reasonable range (200-500 ml)
    if ($donated_ml < 200 || $donated_ml > 500) {
        $_SESSION['error'] = '✗ Donation amount must be between 200 and 500 ml. Standard donation is around 450 ml.';
        header('Location: donordashboard.php');
        exit();
    }

    // Fetch appointment details
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND donor_id = ? AND status = 'confirmed'");
    $stmt->bind_param("ii", $appointment_id, $donor_id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if ($appointment) {
        $conn->begin_transaction();
        try {
            // 1. Mark appointment as completed
            $conn->query("UPDATE appointments SET status = 'completed' WHERE id = $appointment_id");

            // 2. Fetch donor's blood type
            $donor = $conn->query("SELECT blood_type FROM users WHERE id = $donor_id")->fetch_assoc();
            $blood_type = $donor['blood_type'] ?? 'O+';
            $donated_units = $donated_ml; // Use the donated ml amount
            
            // Get donation date from form, default to today if not provided
            $donation_date = isset($_POST['donation_date']) ? $conn->real_escape_string($_POST['donation_date']) : date('Y-m-d');
            
            // 3. Create/Insert donation record to update donor statistics
            $conn->query(
                "INSERT INTO donations (donor_id, blood_type, units, donation_date, status) 
                 VALUES ($donor_id, '$blood_type', $donated_units, '$donation_date', 'completed')"
            );

            // 4. Update blood inventory
            $conn->query("UPDATE blood_inventory SET quantity = quantity + $donated_units WHERE blood_type = '$blood_type'");

            // 5. If this pledge was linked to a recipient, fulfill the request
            if (!empty($appointment['recipient_id'])) {
                $recipient_id = $appointment['recipient_id'];
                
                // Get the medical reason from the original blood request
                $req_stmt = $conn->prepare("SELECT medical_reason FROM blood_requests WHERE donor_id = ? AND recipient_id = ? AND status = 'donor_accepted'");
                $req_stmt->bind_param("ii", $donor_id, $recipient_id);
                $req_stmt->execute();
                $req_result = $req_stmt->get_result()->fetch_assoc();
                $reason = $req_result['medical_reason'] ?? 'Direct Donation';
                
                // Update blood_requests status
                $update_req = $conn->prepare("UPDATE blood_requests SET status = 'fulfilled', units_received = ? WHERE donor_id = ? AND recipient_id = ? AND status = 'donor_accepted'");
                $update_req->bind_param("iii", $donated_units, $donor_id, $recipient_id);
                $update_req->execute();
                
                // Create a transfusion history record so the recipient's profile updates
                $transfusion_date = $donation_date;
                $insert_history = $conn->prepare("INSERT INTO transfusion_history (recipient_id, blood_type, units_received, transfusion_date, transfusion_reason, status) VALUES (?, ?, ?, ?, ?, 'completed')");
                $insert_history->bind_param("isiss", $recipient_id, $blood_type, $donated_units, $transfusion_date, $reason);
                $insert_history->execute();
                
                // Remove the blood from the global inventory since it went directly to the recipient
                $conn->query("UPDATE blood_inventory SET quantity = quantity - $donated_units WHERE blood_type = '$blood_type'");
            }

            $conn->commit();
            $_SESSION['message'] = "✓ Donation marked as completed! You donated {$donated_ml} ml. Your profile statistics have been updated.";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = '✗ Failed to mark donation as completed: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = '✗ Invalid appointment or status.';
    }

    header('Location: donordashboard.php');
    exit();
}
?>