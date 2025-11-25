<?php
require_once __DIR__ . '/_helpers.php';
$in = get_json_input();
$name = trim($in['name'] ?? '');
$email = trim($in['email'] ?? '');
$password = $in['password'] ?? '';
$role = trim($in['role'] ?? 'candidate');
if (!in_array($role, ['candidate','employer','admin'])) $role = 'candidate';
if ($name === '' || $email === '' || $password === '') {
    json_response(['ok'=>false,'error'=>'All fields required'],400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['ok'=>false,'error'=>'Invalid email'],400);

// check existing
$stmt = db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
$stmt->bind_param('s',$email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) json_response(['ok'=>false,'error'=>'Email already registered'],409);

$hash = password_hash($password, PASSWORD_DEFAULT);
$mysqli = db();
$errMsg = '';

// Try to insert including the `role` column.
$ins = $mysqli->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
if ($ins) {
    $ins->bind_param('ssss', $name, $email, $hash, $role);
    if ($ins->execute()) {
        $id = $mysqli->insert_id;
        $_SESSION['user_id'] = $id;
        json_response(['ok' => true, 'user' => ['id' => $id, 'name' => $name, 'email' => $email]]);
    }
    $errMsg = $mysqli->error;
} else {
    // prepare failed (likely missing column) — record error and fall through to fallback
    $errMsg = $mysqli->error;
}

// Fallback: try without role (for older databases)
$ins2 = $mysqli->prepare('INSERT INTO users (name,email,password_hash,created_at) VALUES (?,?,?,NOW())');
if ($ins2) {
    $ins2->bind_param('sss', $name, $email, $hash);
    if ($ins2->execute()) {
        $id = $mysqli->insert_id;
        $_SESSION['user_id'] = $id;
        json_response(['ok' => true, 'user' => ['id' => $id, 'name' => $name, 'email' => $email]]);
    }
    $errMsg = $errMsg ?: $mysqli->error;
} else {
    $errMsg = $errMsg ?: $mysqli->error;
}

json_response(['ok' => false, 'error' => 'Registration failed: ' . ($errMsg ?: 'unknown')], 500);

?>