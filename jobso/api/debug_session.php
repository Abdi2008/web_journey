<?php
// Debug endpoint to inspect session/cookie state. REMOVE or disable in production.
require_once __DIR__ . '/_helpers.php';

// Return session id and session contents for debugging
$sid = session_id();
$cookie = $_COOKIE ?? [];
json_response(['ok'=>true,'session_id'=>$sid,'cookie'=> $cookie, 'session'=> $_SESSION]);

?>
<?php
// Debug helper (remove in production): shows cookies, session id and current user
require_once __DIR__ . '/_helpers.php';
try {
    $cookies = $_COOKIE;
    $sid = session_id();
    $user = null;
    if (is_logged_in()) $user = current_user();
    json_response(['ok'=>true,'cookies'=>$cookies,'session_id'=>$sid,'user'=>$user]);
} catch (Throwable $e) {
    error_log('debug_session error: '.$e->getMessage());
    json_response(['ok'=>false,'error'=>'Server error'],500);
}

?>
