<?php
// db.php — PDO connection helper
session_start();
$config = require __DIR__ . '/config.php';

try {
  if (!empty($config['use_sqlite'])) {
    $pdo = new PDO('sqlite:' . __DIR__ . '/data.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');
  } else {
    $pdo = new PDO($config['db_dsn'], $config['db_user'], $config['db_pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo 'DB connection failed: ' . htmlspecialchars($e->getMessage());
  exit;
}
