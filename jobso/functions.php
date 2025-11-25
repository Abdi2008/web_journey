<?php
require_once __DIR__ . '/config.php';
function esc($s) { return htmlspecialchars($s, ENT_QUOTES); }
function is_logged_in() { return !empty($_SESSION['user_id']); }
function current_user() {
    if (!is_logged_in()) return null;
    $id = (int)$_SESSION['user_id'];
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT id,name,email,role FROM users WHERE id=? LIMIT 1');
    if (!$stmt) {
        // If the DB doesn't have a `role` column (older schema), fallback to a safer select
        if (stripos($mysqli->error, "Unknown column 'role'") !== false || stripos($mysqli->error, 'Unknown column') !== false) {
            error_log('current_user prepare failed (role missing), falling back: ' . $mysqli->error);
            $stmt = $mysqli->prepare('SELECT id,name,email FROM users WHERE id=? LIMIT 1');
            if (!$stmt) {
                error_log('current_user fallback prepare also failed: ' . $mysqli->error);
                return null;
            }
        } else {
            error_log('current_user prepare failed: ' . $mysqli->error);
            return null;
        }
    }
    if (!$stmt->bind_param('i', $id)) {
        error_log('current_user bind_param failed: ' . $mysqli->error);
        return null;
    }
    if (!$stmt->execute()) {
        error_log('current_user execute failed: ' . $mysqli->error);
        return null;
    }
    $res = $stmt->get_result();
    if (!$res) {
        error_log('current_user get_result failed');
        return null;
    }
    return $res->fetch_assoc();
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
?>