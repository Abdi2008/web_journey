<?php
require_once __DIR__ . '/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT j.*, u.name as poster FROM jobs j LEFT JOIN users u ON j.user_id=u.id WHERE j.id=? LIMIT 1');
$stmt->bind_param('i',$id);
$stmt->execute();
$res = $stmt->get_result();
$job = $res->fetch_assoc();
if (!$job) {
    http_response_code(404);
    echo 'Job not found.'; exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?=esc($job['title'])?> - JOBSO</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="container">
    <h2><?=esc($job['title'])?></h2>
    <div class="job-meta"><?=esc($job['company'])?> &middot; <?=esc($job['location'])?> &middot; posted by <?=esc($job['poster'])?></div>
    <section class="job-desc">
      <?=nl2br(esc($job['description']))?>
    </section>
    <p><a href="index.php">Back to list</a></p>
  </main>
</body>
</html>