<?php
require_once __DIR__ . '/_helpers.php';
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
    // if ?mine=1 return only current user's jobs
    if (isset($_GET['mine']) && is_logged_in()) {
        $uid = (int)$_SESSION['user_id'];
        $stmt = db()->prepare('SELECT * FROM jobs WHERE user_id=? ORDER BY posted_at DESC');
        $stmt->bind_param('i',$uid);
    } else {
        $stmt = db()->prepare('SELECT j.*, u.name as poster FROM jobs j LEFT JOIN users u ON j.user_id=u.id ORDER BY posted_at DESC');
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $jobs = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['ok'=>true,'jobs'=>$jobs]);
}

if ($method === 'POST') {
    if (!is_logged_in()) json_response(['ok'=>false,'error'=>'Authentication required'],401);
    $user = current_user();
    if (!$user || !in_array($user['role'], ['employer','admin'])) json_response(['ok'=>false,'error'=>'Only employers can post jobs'],403);
    // Accept form-data (with file) or JSON
    $in = get_json_input();
    $title = trim($in['title'] ?? '');
    $company = trim($in['company'] ?? '');
    $location = trim($in['location'] ?? '');
    $description = trim($in['description'] ?? '');
    $company_logo_filename = null;
    // handle uploaded file if present
    if (!empty($_FILES['company_logo']) && is_uploaded_file($_FILES['company_logo']['tmp_name'])) {
        $f = $_FILES['company_logo'];
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $allowed = ['png','jpg','jpeg','svg','gif'];
        if (!in_array(strtolower($ext), $allowed)) json_response(['ok'=>false,'error'=>'Invalid logo file type'],400);
        $safe = bin2hex(random_bytes(8)) . '.' . $ext;
        $destDir = __DIR__ . '/../assets/company_logos/';
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);
        $dest = $destDir . $safe;
        if (!move_uploaded_file($f['tmp_name'], $dest)) json_response(['ok'=>false,'error'=>'Failed to save logo'],500);
        $company_logo_filename = 'assets/company_logos/' . $safe;
    }
    if ($title === '' || $company === '' || $description === '') json_response(['ok'=>false,'error'=>'Missing fields'],400);
    $uid = (int)$_SESSION['user_id'];
    if ($company_logo_filename) {
        $ins = db()->prepare('INSERT INTO jobs (user_id,title,company,location,company_logo,description,posted_at) VALUES (?,?,?,?,?,NOW())');
        // bind parameters: i s s s s (adjust below)
    }
    // For compatibility with PHP's mysqli bind_param types, build the insert dynamically
    if ($company_logo_filename) {
        $ins = db()->prepare('INSERT INTO jobs (user_id,title,company,location,company_logo,description,posted_at) VALUES (?,?,?,?,?, ?, NOW())');
        $ins->bind_param('isssss', $uid, $title, $company, $location, $company_logo_filename, $description);
    } else {
        $ins = db()->prepare('INSERT INTO jobs (user_id,title,company,location,description,posted_at) VALUES (?,?,?,?,?,NOW())');
        $ins->bind_param('issss',$uid,$title,$company,$location,$description);
    }
    if ($ins->execute()) {
        $id = db()->insert_id;
        json_response(['ok'=>true,'id'=>$id]);
    }
    json_response(['ok'=>false,'error'=>'Insert failed'],500);
}

if ($method === 'DELETE') {
    if (!is_logged_in()) json_response(['ok'=>false,'error'=>'Authentication required'],401);
    // accept id via query param or JSON body
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id === 0) {
        $in = get_json_input();
        $id = (int)($in['id'] ?? 0);
    }
    if ($id === 0) json_response(['ok'=>false,'error'=>'Missing id'],400);
    $uid = (int)$_SESSION['user_id'];
    $del = db()->prepare('DELETE FROM jobs WHERE id=? AND user_id=?');
    $del->bind_param('ii',$id,$uid);
    $del->execute();
    if (db()->affected_rows > 0) json_response(['ok'=>true]);
    json_response(['ok'=>false,'error'=>'Not found or not allowed'],404);
}

json_response(['ok'=>false,'error'=>'Method not allowed'],405);
?>