
<?php
require_once '../connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Capture location and gender fields
    $city = trim($_POST['city'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $gender = trim($_POST['gender'] ?? '');

    // Validation
    if (empty($full_name) || empty($email) || empty($role) || empty($password)) {
        $error = "All required fields must be filled!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (empty($gender)) {
        $error = "Please select your gender!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } 
    // NEW CODE: Validate location fields
    elseif (!empty($latitude) && !is_numeric($latitude)) {
        $error = "Latitude must be a number (e.g., 27.7172)!";
    } elseif (!empty($longitude) && !is_numeric($longitude)) {
        $error = "Longitude must be a number (e.g., 85.3240)!";
    }
    // NEW CODE: Check if BOTH coordinates provided or BOTH empty
    elseif ((empty($latitude) && !empty($longitude)) || (!empty($latitude) && empty($longitude))) {
        $error = "Please provide BOTH latitude and longitude, or leave both empty!";
    }
    else {

        // Check existing email (SECURE)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already registered!";
        } else {

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user with location and gender fields
            $stmt = $conn->prepare("INSERT INTO users 
                (full_name, email, phone, blood_type, password, role, city, latitude, longitude, gender, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->bind_param("sssssssdds", 
                $full_name, $email, $phone, $blood_type, $hashed_password, $role, 
                $city, $latitude, $longitude, $gender
            );

            if ($stmt->execute()) {
                header("Location: login.php?success=1");
                exit();
            } else {
                $error = "Registration failed!";
            }
        }
    }
}

$blood_types = ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - VitalDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../public/css/global-styles.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 650px;">
            <div class="auth-header">
                <h1><i class="bi bi-droplet-fill"></i> VitalDrop</h1>
                <p class="auth-subtitle">Create your account</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="bi bi-exclamation-circle-fill"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Full Name</label>
                    <input type="text" class="form-control" name="full_name" placeholder="Your full name" required
                        value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="your@email.com" required
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" class="form-control" name="phone" placeholder="+977 98xxxxxxxx"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Blood Type</label>
                        <select class="form-select" name="blood_type">
                            <option value="">Select</option>
                            <?php foreach($blood_types as $type): ?>
                                <option value="<?php echo $type; ?>"
                                    <?php echo (($_POST['blood_type'] ?? '') == $type) ? 'selected' : ''; ?>>
                                    <?php echo $type; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Gender</label>
                        <select class="form-select" name="gender" required>
                            <option value="">Select</option>
                            <option value="M" <?php echo (($_POST['gender'] ?? '') == 'M') ? 'selected' : ''; ?>>Male</option>
                            <option value="F" <?php echo (($_POST['gender'] ?? '') == 'F') ? 'selected' : ''; ?>>Female</option>
                            <option value="O" <?php echo (($_POST['gender'] ?? '') == 'O') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-geo-alt"></i> City</label>
                        <input type="text" class="form-control" name="city" placeholder="e.g. Kathmandu"
                            value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-pin-map"></i> Latitude</label>
                        <input type="text" class="form-control" name="latitude" id="latitude" placeholder="e.g. 27.7172"
                            value="<?php echo htmlspecialchars($_POST['latitude'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-pin-map"></i> Longitude</label>
                        <input type="text" class="form-control" name="longitude" id="longitude" placeholder="e.g. 85.3240"
                            value="<?php echo htmlspecialchars($_POST['longitude'] ?? ''); ?>">
                    </div>
                    <div class="col-12 mb-3">
                        <div class="alert alert-light border-0 py-3 mb-0" style="background: rgba(220, 53, 69, 0.05); font-size: 0.85rem;">
                            <i class="bi bi-info-circle-fill text-danger"></i> <strong>How to get your coordinates:</strong>
                            <ol class="mt-2 mb-0 ps-3">
                                <li>Open <a href="https://www.google.com/maps" target="_blank" class="text-danger fw-bold text-decoration-none">Google Maps <i class="bi bi-box-arrow-up-right small"></i></a></li>
                                <li>Right-click on your exact location/house on the map.</li>
                                <li>Click the <strong>coordinates</strong> (the first option in the menu) to copy them.</li>
                                <li>Paste the numbers into the Latitude and Longitude fields above.</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Register As</label>
                    <select class="form-select" name="role" required>
                        <option value="">Select Role</option>
                        <option value="donor" <?php echo (($_POST['role'] ?? '') == 'donor') ? 'selected' : ''; ?>>Blood Donor</option>
                        <option value="recipient" <?php echo (($_POST['role'] ?? '') == 'recipient') ? 'selected' : ''; ?>>Blood Recipient</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Min. 6 characters" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" placeholder="Repeat password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-link-container">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```
