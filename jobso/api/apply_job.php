<?php
require_once __DIR__ . '/_helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
if (!is_logged_in()) json_response(['ok'=>false,'error'=>'Authentication required'],401);
$uid = (int)$_SESSION['user_id'];

if ($method !== 'POST') json_response(['ok'=>false,'error'=>'Method not allowed'],405);

// Accept form-data (file upload) or JSON (cover_text + job_id)
$job_id = 0;
$cover = null;
$resume_path = null;

// If multipart/form-data, job_id may be in $_POST and file in $_FILES
if ($_SERVER['CONTENT_TYPE'] && stripos($_SERVER['CONTENT_TYPE'],'multipart/form-data') !== false) {
    $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $cover = trim($_POST['cover_text'] ?? '');
    if (!empty($_FILES['resume']) && is_uploaded_file($_FILES['resume']['tmp_name'])) {
        $f = $_FILES['resume'];
        $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
        $allowed = ['pdf','doc','docx'];
        if (!in_array(strtolower($ext), $allowed)) json_response(['ok'=>false,'error'=>'Invalid resume file type'],400);
        $safe = bin2hex(random_bytes(8)) . '.' . $ext;
        $destDir = __DIR__ . '/../assets/resumes/';
        if (!is_dir($destDir)) mkdir($destDir,0755,true);
        $dest = $destDir . $safe;
        if (!move_uploaded_file($f['tmp_name'],$dest)) json_response(['ok'=>false,'error'=>'Failed to save resume'],500);
        $resume_path = 'assets/resumes/' . $safe;
    }
} else {
    $in = get_json_input();
    $job_id = (int)($in['job_id'] ?? 0);
    $cover = trim($in['cover_text'] ?? '');
}

if ($job_id <= 0) json_response(['ok'=>false,'error'=>'Missing job_id'],400);

// Check job exists and is active
$check = db()->prepare('SELECT id,is_active FROM jobs WHERE id=? LIMIT 1');
$check->bind_param('i',$job_id);
$check->execute();
$cr = $check->get_result();
$job = $cr->fetch_assoc();
if (!$job) json_response(['ok'=>false,'error'=>'Job not found'],404);
if ($job['is_active']==0) json_response(['ok'=>false,'error'=>'Job is closed'],400);

// Insert application record
$ins = db()->prepare('INSERT INTO applications (user_id,job_id,resume_path,cover_text,applied_at) VALUES (?,?,?,?,NOW())');
if (!$ins) json_response(['ok'=>false,'error'=>'Prepare failed'],500);
$ins->bind_param('iiss',$uid,$job_id,$resume_path,$cover);
if ($ins->execute()) {
    json_response(['ok'=>true,'id'=>db()->insert_id]);
}
json_response(['ok'=>false,'error'=>'Failed to submit application'],500);
