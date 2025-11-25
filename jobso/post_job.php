<?php
require_once __DIR__ . '/functions.php';
require_login();
$user = current_user();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($title === '' || $company === '' || $description === '') {
        $errors[] = 'Title, company and description are required.';
    } else {
        $ins = db()->prepare('INSERT INTO jobs (user_id,title,company,location,description,posted_at) VALUES (?,?,?,?,?,NOW())');
        $uid = (int)$user['id'];
        $ins->bind_param('issss', $uid, $title, $company, $location, $description);
        if ($ins->execute()) {
            header('Location: index.php'); exit;
        } else {
            $errors[] = 'Failed to post job.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Post Job - JOBSO</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="container auth">
    <h2>Post a Job</h2>
    <?php foreach ($errors as $e): ?>
      <div class="error"><?=esc($e)?></div>
    <?php endforeach; ?>
    <form method="post">
      <label>Title<br><input name="title" required></label>
      <label>Company<br><input name="company" required></label>
      <label>Location<br><input name="location"></label>
      <label>Description<br><textarea name="description" rows="8" required></textarea></label>
      <button type="submit">Post Job</button>
    </form>
  </main>
</body>
</html>