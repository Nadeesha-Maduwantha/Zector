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

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$user_id = getUserId();

if (empty($title) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Title and content are required']);
    exit;
}

// Handle image upload
$image_path = null;
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
    
    // Generate unique filename
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('post_') . '.' . $extension;
    $upload_path = '../uploads/' . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
        $image_path = $upload_path;
    }
}

// Insert post with image
$stmt = $conn->prepare("INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $user_id, $title, $content, $image_path);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post created successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create post']);
}

$stmt->close();
$conn->close();
?>