<?php
require_once __DIR__ . '/functions.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $errors[] = 'Email and password required.';
    } else {
        $stmt = db()->prepare('SELECT id,password_hash FROM users WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php'); exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - JOBSO</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="container auth">
    <h2>Login</h2>
    <?php foreach ($errors as $e): ?>
      <div class="error"><?=esc($e)?></div>
    <?php endforeach; ?>
    <form method="post">
      <label>Email<br><input name="email" type="email" required></label>
      <label>Password<br><input name="password" type="password" required></label>
      <button type="submit">Login</button>
    </form>
    <p>No account? <a href="register.php">Register</a></p>
  </main>
</body>
</html>