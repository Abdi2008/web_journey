<?php
require_once __DIR__ . '/_helpers.php';
$in = get_json_input();
$email = trim($in['email'] ?? '');
$password = $in['password'] ?? '';
if ($email === '' || $password === '') json_response(['ok'=>false,'error'=>'Email and password required'],400);

$stmt = db()->prepare('SELECT id,password_hash,name FROM users WHERE email=? LIMIT 1');
$stmt->bind_param('s',$email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    json_response(['ok'=>true,'user'=>['id'=>$user['id'],'name'=>$user['name'],'email'=>$email]]);
}
json_response(['ok'=>false,'error'=>'Invalid credentials'],401);
?>