<?php
require_once __DIR__ . '/functions.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
  $role = trim($_POST['role'] ?? 'candidate');
  if (!in_array($role, ['candidate','employer','admin'])) $role = 'candidate';
    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is invalid.';
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
      $mysqli = db();
      // Try to insert including role (if schema has the column)
      $ins = $mysqli->prepare('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,NOW())');
      if ($ins) {
        $ins->bind_param('ssss', $name, $email, $hash, $role);
        if ($ins->execute()) {
          $_SESSION['user_id'] = $mysqli->insert_id;
          header('Location: /JOBSO/frontend/index.html'); exit;
        }
        // record error and fall through to fallback
        error_log('register.php insert with role failed: ' . $mysqli->error);
      }
      // Fallback: older DB without role column
      $ins2 = $mysqli->prepare('INSERT INTO users (name,email,password_hash,created_at) VALUES (?,?,?,NOW())');
      if ($ins2) {
        $ins2->bind_param('sss', $name, $email, $hash);
        if ($ins2->execute()) {
          $_SESSION['user_id'] = $mysqli->insert_id;
          header('Location: /JOBSO/frontend/index.html'); exit;
        }
        error_log('register.php fallback insert failed: ' . $mysqli->error);
      }
      $errors[] = 'Registration failed.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register - JOBSO</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <main class="container auth">
    <h2>Create account</h2>
    <?php foreach ($errors as $e): ?>
      <div class="error"><?=esc($e)?></div>
    <?php endforeach; ?>
    <form method="post">
      <label>Name<br><input name="name" required></label>
      <label>Email<br><input name="email" type="email" required></label>
      <label>Account type<br>
        <select name="role">
          <option value="candidate">Job Seeker</option>
          <option value="employer">Employer</option>
        </select>
      </label>
      <label>Password<br><input name="password" type="password" required></label>
      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
  </main>
</body>
</html>