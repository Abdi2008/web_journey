<?php
require_once __DIR__ . '/_helpers.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_response(['ok'=>false,'error'=>'Method not allowed'],405);
$id = (int)($_GET['id'] ?? 0);
if ($id === 0) json_response(['ok'=>false,'error'=>'Missing id'],400);
$stmt = db()->prepare('SELECT j.*, u.name as poster FROM jobs j LEFT JOIN users u ON j.user_id=u.id WHERE j.id=? LIMIT 1');
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$job = $res->fetch_assoc();
if (!$job) json_response(['ok'=>false,'error'=>'Not found'],404);
json_response(['ok'=>true,'job'=>$job]);
?>