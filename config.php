<?php
// Copy this file to config.php and fill DB credentials for MySQL/MariaDB.
// Example local dev: host=127.0.0.1; dbname=restaurant; user=root; pass=secret
return [
  'db_dsn'  => 'mysql:host=127.0.0.1;dbname=restaurant;charset=utf8mb4',
  'db_user' => 'root',
  'db_pass' => 'password_here',
  // Set to true to use SQLite for quick demo (file ./data.sqlite). If true, db_dsn is ignored.
  'use_sqlite' => true
];
