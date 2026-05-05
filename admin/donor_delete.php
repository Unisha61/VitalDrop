<?php
require_once '../connect.php';
require_role('admin');

$donor_id = sanitize_input($_GET['id'] ?? '');
if (empty($donor_id)) {
    header("Location: donor_manage.php");
    exit();
}

$conn->query("UPDATE users SET is_active=0 WHERE id=$donor_id AND role='donor'");
header("Location: donor_manage.php");
exit();
?>
