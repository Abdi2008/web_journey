<?php
require_once __DIR__ . '/_helpers.php';
// Admin-only user management API
$method = $_SERVER['REQUEST_METHOD'];
$user = current_user();
if (!$user || $user['role'] !== 'admin') {
    json_response(['ok'=>false,'error'=>'Admin access required'],403);
}

if ($method === 'GET') {
    $res = db()->query("SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC");
    $users = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['ok'=>true,'users'=>$users]);
}

if ($method === 'POST') {
    $in = get_json_input();
    $id = (int)($in['id'] ?? 0);
    $role = trim($in['role'] ?? '');
    if ($id === 0 || $role === '') json_response(['ok'=>false,'error'=>'Missing id or role'],400);
    if (!in_array($role, ['candidate','employer','admin'])) json_response(['ok'=>false,'error'=>'Invalid role'],400);
    // prevent removing last admin: ensure at least one admin remains
    if ($role !== 'admin') {
        // check if target is currently admin
        $stmt = db()->prepare('SELECT role FROM users WHERE id=? LIMIT 1');
        $stmt->bind_param('i',$id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        if ($r && $r['role'] === 'admin') {
            $check = db()->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'")->fetch_assoc();
            if ($check && $check['c'] <= 1) json_response(['ok'=>false,'error'=>'Cannot remove the last admin'],400);
        }
    }
    $upd = db()->prepare('UPDATE users SET role=? WHERE id=?');
    $upd->bind_param('si',$role,$id);
    if ($upd->execute()) json_response(['ok'=>true]);
    json_response(['ok'=>false,'error'=>'Update failed'],500);
}

if ($method === 'DELETE') {
    // accept id via query or body
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id === 0) {
        $in = get_json_input();
        $id = (int)($in['id'] ?? 0);
    }
    if ($id === 0) json_response(['ok'=>false,'error'=>'Missing id'],400);
    if ($id === (int)$user['id']) json_response(['ok'=>false,'error'=>'Cannot delete yourself'],400);
    // prevent deleting last admin
    $stmt = db()->prepare('SELECT role FROM users WHERE id=? LIMIT 1');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if ($r && $r['role'] === 'admin') {
        $check = db()->query("SELECT COUNT(*) AS c FROM users WHERE role='admin'")->fetch_assoc();
        if ($check && $check['c'] <= 1) json_response(['ok'=>false,'error'=>'Cannot delete the last admin'],400);
    }
    $del = db()->prepare('DELETE FROM users WHERE id=?');
    $del->bind_param('i',$id);
    $del->execute();
    if (db()->affected_rows > 0) json_response(['ok'=>true]);
    json_response(['ok'=>false,'error'=>'Not found'],404);
}

json_response(['ok'=>false,'error'=>'Method not allowed'],405);
?>