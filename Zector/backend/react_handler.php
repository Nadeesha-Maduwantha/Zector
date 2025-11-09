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
$post_id = intval($_POST['post_id'] ?? 0);
$reaction = $_POST['reaction'] ?? '';

if (!in_array($reaction, ['like', 'dislike']) || $post_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Check if user already reacted
$stmt = $conn->prepare("SELECT reaction FROM post_reactions WHERE post_id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    
    if ($existing['reaction'] === $reaction) {
        // Remove reaction if clicking same button
        $stmt = $conn->prepare("DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
    } else {
        // Update to new reaction
        $stmt = $conn->prepare("UPDATE post_reactions SET reaction = ? WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $reaction, $post_id, $user_id);
        $stmt->execute();
    }
} else {
    // Insert new reaction
    $stmt = $conn->prepare("INSERT INTO post_reactions (post_id, user_id, reaction) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $reaction);
    $stmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Reaction updated']);

$stmt->close();
$conn->close();
?>