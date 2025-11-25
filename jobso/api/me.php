<?php
require_once __DIR__ . '/_helpers.php';

// Defensive: ensure we always return valid JSON even if something goes wrong
try {
	if (!is_logged_in()) {
		json_response(['ok' => true, 'user' => null]);
	}
	$user = current_user();
	if ($user) {
		json_response(['ok' => true, 'user' => $user]);
	}
	// If current_user couldn't be resolved, return null user rather than erroring
	json_response(['ok' => true, 'user' => null]);
} catch (Throwable $e) {
	error_log('api/me.php exception: ' . $e->getMessage());
	json_response(['ok' => false, 'error' => 'Server error'], 500);
}
?>