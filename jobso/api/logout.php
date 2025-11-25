<?php
require_once __DIR__ . '/_helpers.php';
session_destroy();
json_response(['ok'=>true]);
?>