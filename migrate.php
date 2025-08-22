<?php
// migrate.php — add new columns for v2 if absent
require __DIR__ . '/db.php';

function column_exists_sqlite($pdo, $table, $col) {
  $st = $pdo->query("PRAGMA table_info(".$table.")");
  foreach ($st as $row) if (strcasecmp($row['name'],$col)==0) return true;
  return false;
}

$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
  if (!column_exists_sqlite($pdo, 'tables', 'area')) {
    $pdo->exec("ALTER TABLE tables ADD COLUMN area TEXT NOT NULL DEFAULT 'hall'");
    echo "Added tables.area\n";
  }
  if (!column_exists_sqlite($pdo, 'tables', 'width')) {
    $pdo->exec("ALTER TABLE tables ADD COLUMN width INTEGER DEFAULT 42");
    $pdo->exec("ALTER TABLE tables ADD COLUMN height INTEGER DEFAULT 42");
    echo "Added tables.width/height\n";
  }
  if (!column_exists_sqlite($pdo, 'reservations', 'deleted_at')) {
    $pdo->exec("ALTER TABLE reservations ADD COLUMN deleted_at DATETIME DEFAULT NULL");
    echo "Added reservations.deleted_at\n";
  }
  echo "SQLite migration complete.";
} else {
  // MySQL snippet (run separately in your DB if needed)
  echo "For MySQL, run:\n";
  echo "ALTER TABLE tables ADD COLUMN area VARCHAR(16) NOT NULL DEFAULT 'hall';\n";
  echo "ALTER TABLE tables ADD COLUMN width INT DEFAULT 42;\n";
  echo "ALTER TABLE tables ADD COLUMN height INT DEFAULT 42;\n";
  echo "ALTER TABLE reservations ADD COLUMN deleted_at DATETIME NULL;\n";
}
