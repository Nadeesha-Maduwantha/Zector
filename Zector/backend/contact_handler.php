<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// In a real application, you would:
// 1. Send an email to admin
// 2. Save to database
// 3. Send confirmation email to user

// For now, we'll just log to a file
$contact_data = [
    'date' => date('Y-m-d H:i:s'),
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message
];

$log_entry = json_encode($contact_data) . "\n";
file_put_contents('contacts.log', $log_entry, FILE_APPEND);

echo json_encode([
    'success' => true, 
    'message' => 'Thank you for your message! We will get back to you soon.'
]);
?>
