<?php
/**
 * Safe one-time migration to add `role` column to `users` table.
 * Usage:
 *  - From browser: visit `http://localhost/JOBSO/migrations/add_role_column.php` to see status.
 *    Add `?confirm=1` to actually apply: `...?confirm=1`
 *  - From CLI: `php migrations/add_role_column.php` will attempt to apply immediately.
 */
require_once __DIR__ . '/../config.php';

$mysqli = db();
if ($mysqli->connect_errno) {
    echo "DB connection failed: " . $mysqli->connect_error . "\n";
    exit(1);
}

$check = $mysqli->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check === false) {
    echo "Could not inspect table structure: " . $mysqli->error . "\n";
    exit(1);
}

if ($check->num_rows > 0) {
    echo "Column 'role' already exists on 'users'. Nothing to do.\n";
    exit(0);
}

$willApply = false;
// CLI runs will apply immediately; web requests require ?confirm=1
if (php_sapi_name() === 'cli') {
    $willApply = true;
} else {
    $confirm = isset($_GET['confirm']) ? (int)$_GET['confirm'] : 0;
    if ($confirm === 1) $willApply = true;
}

if (!$willApply) {
    echo "This will ALTER TABLE `users` to add column `role` ENUM('candidate','employer','admin') NOT NULL DEFAULT 'candidate'.\n";
    echo "Re-run with '?confirm=1' to apply, or run from CLI: `php migrations/add_role_column.php`.\n";
    exit(0);
}

$sql = "ALTER TABLE users ADD COLUMN role ENUM('candidate','employer','admin') NOT NULL DEFAULT 'candidate'";
if ($mysqli->query($sql) === true) {
    echo "Successfully added 'role' column to 'users'.\n";
    exit(0);
} else {
    echo "Failed to add 'role' column: " . $mysqli->error . "\n";
    error_log('migration add_role_column failed: ' . $mysqli->error);
    exit(1);
}

?>
