<?php
require_once '../connect.php';

session_destroy();
// Redirect using BASE_PATH constant
header("Location: " . BASE_PATH . "/public/home.html");
exit();
?>
