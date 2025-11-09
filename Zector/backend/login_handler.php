<?php
header('Content-Type: application/json');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter username and password']);
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password, profile_photo FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful!',
            'username' => $user['username'],
            'profile_photo' => $user['profile_photo']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

$stmt->close();
$conn->close();
exit();
?>

$stmt->close();
$conn->close();
?>