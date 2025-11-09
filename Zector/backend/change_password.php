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
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
    exit;
}

// Verify current password
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

if (!password_verify($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    exit;
}

// Update password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashed_password, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to change password']);
}

$stmt->close();
$conn->close();
?>
