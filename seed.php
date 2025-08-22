<?php
require __DIR__ . '/db.php';

$pdo->exec(file_get_contents(__DIR__.'/schema.sql'));

$email = 'admin@example.com';
$pass = password_hash('admin123', PASSWORD_DEFAULT);
$name = 'Admin';

$stmt = $pdo->prepare('INSERT OR IGNORE INTO users (email,password_hash,full_name) VALUES (?,?,?)');
$stmt->execute([$email, $pass, $name]);

echo "Seeded. Login with admin@example.com / admin123";
