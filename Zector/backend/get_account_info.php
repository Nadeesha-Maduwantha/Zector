<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = getUserId();

// Get user information
$stmt = $conn->prepare("SELECT id, username, email, profile_photo, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Get user statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_posts FROM posts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_posts = $stmt->get_result()->fetch_assoc()['total_posts'];
$stmt->close();

// Get total likes
$stmt = $conn->prepare("SELECT COUNT(*) as total_likes FROM post_reactions pr 
                        JOIN posts p ON pr.post_id = p.id 
                        WHERE p.user_id = ? AND pr.reaction = 'like'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_likes = $stmt->get_result()->fetch_assoc()['total_likes'];
$stmt->close();

// Get total dislikes
$stmt = $conn->prepare("SELECT COUNT(*) as total_dislikes FROM post_reactions pr 
                        JOIN posts p ON pr.post_id = p.id 
                        WHERE p.user_id = ? AND pr.reaction = 'dislike'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_dislikes = $stmt->get_result()->fetch_assoc()['total_dislikes'];
$stmt->close();

echo json_encode([
    'success' => true,
    'user' => $user,
    'stats' => [
        'total_posts' => $total_posts,
        'total_likes' => $total_likes,
        'total_dislikes' => $total_dislikes
    ]
]);

$conn->close();
?>
