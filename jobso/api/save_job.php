<?php
require_once __DIR__ . '/_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
if (!is_logged_in()) json_response(['ok'=>false,'error'=>'Authentication required'],401);
$uid = (int)$_SESSION['user_id'];

if ($method === 'GET') {
    // list saved jobs for current user
    $mysqli = db();
    $stmt = $mysqli->prepare('SELECT s.id, s.job_id, s.saved_at, j.title, j.company, j.location FROM saved_jobs s JOIN jobs j ON s.job_id=j.id WHERE s.user_id=? ORDER BY s.saved_at DESC');
    if (!$stmt) {
        error_log('api/save_job GET prepare failed: ' . $mysqli->error);
        json_response(['ok'=>false,'error'=>'DB prepare failed'],500);
    }
    if (!$stmt->bind_param('i', $uid)) {
        error_log('api/save_job GET bind_param failed: ' . $mysqli->error);
        json_response(['ok'=>false,'error'=>'DB bind failed'],500);
    }
    if (!$stmt->execute()) {
        error_log('api/save_job GET execute failed: ' . $mysqli->error);
        json_response(['ok'=>false,'error'=>'DB execute failed'],500);
    }
    $res = $stmt->get_result();
    if ($res === false) {
        error_log('api/save_job GET get_result failed');
        json_response(['ok'=>false,'error'=>'DB get_result failed'],500);
    }
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['ok'=>true,'saved'=>$rows]);
}

if ($method === 'POST') {
    $in = get_json_input();
    $job_id = (int)($in['job_id'] ?? 0);
    if ($job_id <= 0) json_response(['ok'=>false,'error'=>'Missing job_id'],400);
    // insert if not exists
    $mysqli = db();
    $ins = $mysqli->prepare('INSERT INTO saved_jobs (user_id,job_id,saved_at) VALUES (?,?,NOW())');
    if (!$ins) {
        error_log('api/save_job POST prepare failed: ' . $mysqli->error);
        json_response(['ok'=>false,'error'=>'DB prepare failed'],500);
    }
    if (!$ins->bind_param('ii',$uid,$job_id)) {
        error_log('api/save_job POST bind_param failed: ' . $mysqli->error);
        json_response(['ok'=>false,'error'=>'DB bind failed'],500);
    }
    if (!$ins->execute()) {
        // handle duplicate key gracefully
        $err = $mysqli->error;
        error_log('api/save_job POST execute failed: ' . $err);
        if (stripos($err,'Duplicate') !== false || stripos($err,'duplicate') !== false) {
            json_response(['ok'=>true,'note'=>'Already saved']);
        }
        json_response(['ok'=>false,'error'=>'Could not save job'],500);
    }
    json_response(['ok'=>true]);
}

if ($method === 'DELETE') {
    // accept id param or job_id
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id === 0) {
        $in = get_json_input();
        $id = (int)($in['id'] ?? 0);
        $job_id = (int)($in['job_id'] ?? 0);
    } else {
        $job_id = 0;
    }
    if ($id === 0 && $job_id === 0) json_response(['ok'=>false,'error'=>'Missing id or job_id'],400);
    $mysqli = db();
    if ($id) {
        $del = $mysqli->prepare('DELETE FROM saved_jobs WHERE id=? AND user_id=?');
        if (!$del) { error_log('api/save_job DELETE prepare failed: ' . $mysqli->error); json_response(['ok'=>false,'error'=>'DB prepare failed'],500); }
        $del->bind_param('ii',$id,$uid);
    } else {
        $del = $mysqli->prepare('DELETE FROM saved_jobs WHERE job_id=? AND user_id=?');
        if (!$del) { error_log('api/save_job DELETE prepare failed: ' . $mysqli->error); json_response(['ok'=>false,'error'=>'DB prepare failed'],500); }
        $del->bind_param('ii',$job_id,$uid);
    }
    if (!$del->execute()) {
        error_log('api/save_job DELETE execute failed: ' . $mysqli->error);
        json_response(['ok'=>false,'error'=>'DB execute failed'],500);
    }
    if ($mysqli->affected_rows>0) json_response(['ok'=>true]);
    json_response(['ok'=>false,'error'=>'Not found'],404);
}

json_response(['ok'=>false,'error'=>'Method not allowed'],405);
