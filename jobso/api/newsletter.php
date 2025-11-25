<?php
// Simple newsletter subscription endpoint
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$email = isset($data['email']) ? trim($data['email']) : null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email']);
    exit;
}

// Make sure table exists - assume migrations were run; try insert and return friendly errors
try {
    $stmt = $mysqli->prepare('INSERT INTO newsletter_subscribers (email, subscribed_at) VALUES (?, NOW())');
    $stmt->bind_param('s', $email);
    $ok = $stmt->execute();
    if (!$ok) {
        // Duplicate or other error
        if ($mysqli->errno === 1062) { // duplicate
            echo json_encode(['success' => true, 'message' => 'Already subscribed']);
            exit;
        }
        throw new Exception('Insert failed: ' . $mysqli->error);
    }
    echo json_encode(['success' => true, 'message' => 'Subscribed']);
} catch (Exception $e) {
    http_response_code(500);
    error_log('newsletter error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

