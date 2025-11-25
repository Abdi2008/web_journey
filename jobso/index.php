<?php
require_once __DIR__ . '/functions.php';
$jobs = [];
$res = db()->query("SELECT j.*, u.name as poster FROM jobs j LEFT JOIN users u ON j.user_id=u.id ORDER BY posted_at DESC LIMIT 50");
while ($r = $res->fetch_assoc()) $jobs[] = $r;
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>JOBSO - Job Portal</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <h1><a href="/JOBSO/">JOBSO</a></h1>
      <nav>
        <a href="index.php">Home</a>
        <?php if ($user): ?>
          <a href="post_job.php">Post Job</a>
          <a href="dashboard.php">Dashboard</a>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="register.php">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container">
    <h2>Latest Jobs</h2>
    <?php if (empty($jobs)): ?>
      <p>No jobs posted yet.</p>
    <?php else: ?>
      <ul class="job-list">
      <?php foreach ($jobs as $job): ?>
        <li>
          <a class="job-title" href="view_job.php?id=<?=$job['id']?>"><?=esc($job['title'])?></a>
          <div class="job-meta"><?=esc($job['company'])?> &middot; <?=esc($job['location'])?> &middot; posted by <?=esc($job['poster'])?></div>
        </li>
      <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </main>
  <script src="js/app.js"></script>
</body>
</html>