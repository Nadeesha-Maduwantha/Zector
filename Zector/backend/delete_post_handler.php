<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$post_id = intval($_GET['id'] ?? 0);
$user_id = getUserId();

if ($post_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

// Get post image before deleting
$stmt = $conn->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
    $image_path = $post['image'];
    $stmt->close();
    
    // Delete the post
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        // Delete image file if exists
        if ($image_path && file_exists($image_path)) {
            unlink($image_path);
        }
        echo json_encode(['success' => true, 'message' => 'Post deleted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
}

$stmt->close();
$conn->close();
?>