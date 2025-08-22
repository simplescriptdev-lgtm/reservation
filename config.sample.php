<?php
// Configuration for DB connection
return [
  'db_dsn'  => 'mysql:host=127.0.0.1;dbname=restaurant;charset=utf8mb4',
  'db_user' => 'root',
  'db_pass' => 'password_here',
  // Set true for quick demo on SQLite (file ./data.sqlite). If true, db_dsn is ignored.
  'use_sqlite' => true
];
