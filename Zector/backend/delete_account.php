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

// Get all user's post images to delete
$stmt = $conn->prepare("SELECT image FROM posts WHERE user_id = ? AND image IS NOT NULL");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    if ($row['image']) {
        $images[] = $row['image'];
    }
}
$stmt->close();

// Delete all user's posts (reactions will be deleted via CASCADE)
$stmt = $conn->prepare("DELETE FROM posts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Delete user account
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Delete all post images
    foreach ($images as $image) {
        if (file_exists($image)) {
            unlink($image);
        }
    }
    
    // Destroy session
    session_destroy();
    
    echo json_encode(['success' => true, 'message' => 'Account deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete account']);
}

$stmt->close();
$conn->close();
?>
