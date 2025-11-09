<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($username) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Check username or email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
    exit;
}
$stmt->close();

// Profile photo upload
$profile_photo = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $file_type = $_FILES['profile_photo']['type'];
    $file_size = $_FILES['profile_photo']['size'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
        exit;
    }
    
    if ($file_size > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 2MB']);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('profile_') . '.' . $extension;
    $upload_path = '../uploads/profiles/' . $filename;
    
    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
        $profile_photo = $upload_path;
    }
}

// Hash password and insert new user
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, profile_photo, password) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $username, $email, $profile_photo, $hashed_password);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration successful! Redirecting to login...']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>