<?php
header('Content-Type: application/json');
require_once 'config.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = getUserId();

// Get current user info
$stmt = $conn->prepare("SELECT username, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check if requesting single post
if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']);
    $query = "SELECT p.*, u.username, u.profile_photo,
              (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction = 'like') as likes,
              (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction = 'dislike') as dislikes,
              (SELECT reaction FROM post_reactions WHERE post_id = p.id AND user_id = ?) as user_reaction
              FROM posts p
              JOIN users u ON p.user_id = u.id
              WHERE p.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $post = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'post' => $post
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
    }
    $stmt->close();
    exit;
}

// Check if requesting only user's own posts
if (isset($_GET['user_posts']) && $_GET['user_posts'] === 'true') {
    $query = "SELECT p.*, u.username, u.profile_photo,
              (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction = 'like') as likes,
              (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction = 'dislike') as dislikes
              FROM posts p
              JOIN users u ON p.user_id = u.id
              WHERE p.user_id = ?
              ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'posts' => $posts
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

// Get all posts
$query = "SELECT p.*, u.username, u.profile_photo,
          (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction = 'like') as likes,
          (SELECT COUNT(*) FROM post_reactions WHERE post_id = p.id AND reaction = 'dislike') as dislikes,
          (SELECT reaction FROM post_reactions WHERE post_id = p.id AND user_id = ?) as user_reaction
          FROM posts p
          JOIN users u ON p.user_id = u.id
          ORDER BY p.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

echo json_encode([
    'success' => true,
    'username' => $user['username'],
    'profile_photo' => $user['profile_photo'],
    'user_id' => $user_id,
    'posts' => $posts
]);

$stmt->close();
$conn->close();
?>