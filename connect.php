<?php
// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vitaldrops');

// Application base path for proper URL resolution
if (!defined('BASE_PATH')) {
    // Get the base directory from the actual file location
    $app_root = dirname(__FILE__);  // /xampp/htdocs/VitalDrop6th
    $app_name = basename($app_root);  // VitalDrop6th
    define('BASE_PATH', '/' . $app_name);
}

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Session management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to escape for database
function escape_db_input($data) {
    global $conn;
    return $conn->real_escape_string($data);
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get user role
function get_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

// Function to redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: auth/login.php");
        exit();
    }
}

// Function to require specific role
function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        header("Location: ../public/home.html");
        exit();
    }
}
?>
