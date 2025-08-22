<?php
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    header('Location: /dashboard.php');
    exit;
  } else {
    $error = 'Невірний email або пароль';
  }
}
?>
<!doctype html>
<html lang="uk">
<head>
  <meta charset="utf-8">
  <title>Вхід — Резервації</title>
  <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="auth">
  <div class="auth-card">
    <h1>Вхід</h1>
    <?php if (!empty($error)): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Email
        <input name="email" type="email" placeholder="admin@example.com" required>
      </label>
      <label>Пароль
        <input name="password" type="password" placeholder="admin123" required>
      </label>
      <button type="submit">Увійти</button>
      <p class="hint">Демо: admin@example.com / admin123</p>
    </form>
  </div>
</body>
</html>
