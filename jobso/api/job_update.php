<?php
require_once __DIR__ . '/_helpers.php';
// Update a job (owner or admin)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['ok'=>false,'error'=>'Method not allowed'],405);
if (!is_logged_in()) json_response(['ok'=>false,'error'=>'Authentication required'],401);
$user = current_user();
$in = get_json_input();
$id = (int)($in['id'] ?? 0);
if ($id === 0) json_response(['ok'=>false,'error'=>'Missing id'],400);
// fetch job
$stmt = db()->prepare('SELECT id,user_id FROM jobs WHERE id=? LIMIT 1');
$stmt->bind_param('i',$id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
if (!$job) json_response(['ok'=>false,'error'=>'Not found'],404);
// permission: owner or admin
if ($user['role'] !== 'admin' && (int)$job['user_id'] !== (int)$user['id']) json_response(['ok'=>false,'error'=>'Not allowed'],403);
// fields to update
$fields = [];
$params = [];
$types = '';
if (isset($in['title'])) { $fields[] = 'title=?'; $params[] = $in['title']; $types .= 's'; }
if (isset($in['company'])) { $fields[] = 'company=?'; $params[] = $in['company']; $types .= 's'; }
if (isset($in['location'])) { $fields[] = 'location=?'; $params[] = $in['location']; $types .= 's'; }
if (isset($in['description'])) { $fields[] = 'description=?'; $params[] = $in['description']; $types .= 's'; }
if (isset($in['is_active'])) { $fields[] = 'is_active=?'; $params[] = (int)$in['is_active']; $types .= 'i'; }

// handle optional logo upload (multipart)
$company_logo_filename = null;
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
    $fields[] = 'company_logo=?'; $params[] = $company_logo_filename; $types .= 's';
}

if (empty($fields)) json_response(['ok'=>false,'error'=>'Nothing to update'],400);
$sql = 'UPDATE jobs SET ' . implode(', ', $fields) . ' WHERE id=? LIMIT 1';
$params[] = $id; $types .= 'i';
$stmt = db()->prepare($sql);
// bind params dynamically
$bind_names[] = $types;
for ($i=0;$i<count($params);$i++){
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
}
call_user_func_array([$stmt,'bind_param'],$bind_names);
if ($stmt->execute()) json_response(['ok'=>true]);
json_response(['ok'=>false,'error'=>'Update failed'],500);
?>