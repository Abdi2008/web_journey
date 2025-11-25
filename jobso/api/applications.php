<?php
require_once __DIR__ . '/_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') json_response(['ok'=>false,'error'=>'Method not allowed'],405);
if (!is_logged_in()) json_response(['ok'=>false,'error'=>'Authentication required'],401);
$uid = (int)$_SESSION['user_id'];

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
if ($job_id <= 0) json_response(['ok'=>false,'error'=>'Missing job_id'],400);

// verify job exists and that current user owns it or is admin
$check = db()->prepare('SELECT user_id FROM jobs WHERE id=? LIMIT 1');
$check->bind_param('i',$job_id);
$check->execute();
$cr = $check->get_result();
$job = $cr->fetch_assoc();
if (!$job) json_response(['ok'=>false,'error'=>'Job not found'],404);

$user = current_user();
if (!$user) json_response(['ok'=>false,'error'=>'Auth failed'],401);
if (!($user['role']==='admin' || (int)$job['user_id'] === (int)$user['id'])) {
    json_response(['ok'=>false,'error'=>'Not allowed'],403);
}

$stmt = db()->prepare('SELECT a.id,a.user_id,a.resume_path,a.cover_text,a.applied_at,u.name AS applicant_name,u.email AS applicant_email FROM applications a LEFT JOIN users u ON a.user_id=u.id WHERE a.job_id=? ORDER BY a.applied_at DESC');
$stmt->bind_param('i',$job_id);
$stmt->execute();
$res = $stmt->get_result();
$apps = $res->fetch_all(MYSQLI_ASSOC);
json_response(['ok'=>true,'applications'=>$apps]);

?>
