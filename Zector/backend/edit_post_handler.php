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

$post_id = intval($_POST['post_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$user_id = getUserId();
$current_image = $_POST['current_image'] ?? null;
$delete_image = $_POST['delete_image'] ?? '0';

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit;
}

// Get current post data
$stmt = $conn->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$post = $result->fetch_assoc();
$old_image = $post['image'];
$stmt->close();

// Handle image update
$new_image = $old_image;

// Check if user wants to delete the image
if ($delete_image === '1') {
    if ($old_image && file_exists($old_image)) {
        unlink($old_image);
    }
    $new_image = null;
}

// Check if new image uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    $file_type = $_FILES['image']['type'];
    $file_size = $_FILES['image']['size'];
    
    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed']);
        exit;
    }
    
    if ($file_size > $max_size) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']);
        exit;
    }
    
    // Delete old image if exists
    if ($old_image && file_exists($old_image)) {
        unlink($old_image);
    }
    
    // Generate unique filename
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('post_') . '.' . $extension;
    $upload_path = '../uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $new_image = $upload_path;
    }
}

// Update post
$stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ? AND user_id = ?");
$stmt->bind_param("sssii", $title, $content, $new_image, $post_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update post']);
}

$stmt->close();
$conn->close();
?>