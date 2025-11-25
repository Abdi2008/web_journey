<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = current_user();
$uid = (int)$user['id'];
if (isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $did = (int)$_GET['id'];
    $del = db()->prepare('DELETE FROM jobs WHERE id=? AND user_id=?');
    $del->bind_param('ii',$did,$uid);
    $del->execute();
    header('Location: dashboard.php'); exit;
}
$stmt = db()->prepare('SELECT * FROM jobs WHERE user_id=? ORDER BY posted_at DESC');
$stmt->bind_param('i',$uid);
$stmt->execute();
$res = $stmt->get_result();
$jobs = $res->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard - JOBSO</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="container">
    <h2>Your Jobs</h2>
    <?php if (empty($jobs)): ?>
      <p>No jobs posted yet. <a href="post_job.php">Post one</a>.</p>
    <?php else: ?>
      <ul class="job-list">
      <?php foreach ($jobs as $job): ?>
        <li>
          <a class="job-title" href="view_job.php?id=<?=$job['id']?>"><?=esc($job['title'])?></a>
          <div class="job-meta"><?=esc($job['company'])?> &middot; <?=esc($job['location'])?></div>
          <div><a href="dashboard.php?action=delete&id=<?=$job['id']?>" onclick="return confirm('Delete?')">Delete</a></div>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
</body>
</html>