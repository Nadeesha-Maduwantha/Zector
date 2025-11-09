<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'config.php';

$authenticated = isLoggedIn();
$user_id = getUserId();

$response = [
    'authenticated' => $authenticated,
    'user_id' => $user_id
];

echo json_encode($response);
exit();
?>