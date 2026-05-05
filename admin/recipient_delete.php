<?php
require_once '../connect.php';
require_role('admin');

$recipient_id = sanitize_input($_GET['id'] ?? '');
if (empty($recipient_id)) {
    header("Location: recipient_manage.php");
    exit();
}

$conn->query("UPDATE users SET is_active=0 WHERE id=$recipient_id AND role='recipient'");
header("Location: recipient_manage.php");
exit();
?>
