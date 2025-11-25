<?php
session_start();
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'jobso';
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    // If this is an API request, return JSON error instead of plain HTML/text
    $req = $_SERVER['REQUEST_URI'] ?? '';
    if (stripos($req, '/api/') !== false) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'DB connection failed']);
        exit;
    }
    // Non-API: fall back to original behavior
    die('DB connection failed: ' . $mysqli->connect_error);
}
function db() {
    global $mysqli;
    return $mysqli;
}
?>