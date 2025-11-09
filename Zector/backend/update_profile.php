<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = getUserId();
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$remove_photo = $_POST['remove_photo'] ?? '0';

if (empty($username) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Username and email are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Get current user data
$stmt = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$current_user = $result->fetch_assoc();
$old_photo = $current_user['profile_photo'];
$stmt->close();

// Check if username or email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
$stmt->bind_param("ssi", $username, $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already in use']);
    exit;
}
$stmt->close();

//  profile photo
$new_photo = $old_photo;

// Remove photo if requested
if ($remove_photo === '1') {
    if ($old_photo && file_exists($old_photo)) {
        unlink($old_photo);
    }
    $new_photo = null;
}

// Upload new photo if provided
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
    
    // Delete old photo
    if ($old_photo && file_exists($old_photo)) {
        unlink($old_photo);
    }
    
    // Upload new photo
    $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('profile_') . '.' . $extension;
    $upload_path = '../uploads/profiles/' . $filename;
    
    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
        $new_photo = $upload_path;
    }
}

// Update user profile
$stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_photo = ? WHERE id = ?");
$stmt->bind_param("sssi", $username, $email, $new_photo, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
?>
