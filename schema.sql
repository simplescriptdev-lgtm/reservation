-- schema.sql (works for SQLite / MySQL with minor differences)
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(190) NOT NULL
);

CREATE TABLE IF NOT EXISTS tables (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  number INTEGER NOT NULL,
  seats INTEGER NOT NULL DEFAULT 2,
  x INTEGER NOT NULL DEFAULT 50,
  y INTEGER NOT NULL DEFAULT 50,
  shape TEXT NOT NULL DEFAULT 'circle', -- 'circle' or 'rect'
  width INTEGER DEFAULT 42, -- 30% smaller icons
  height INTEGER DEFAULT 42,
  area TEXT NOT NULL DEFAULT 'hall' -- 'hall' or 'terrace'
);

CREATE UNIQUE INDEX IF NOT EXISTS idx_tables_number ON tables(number);

CREATE TABLE IF NOT EXISTS reservations (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  table_id INTEGER NOT NULL,
  res_date DATE NOT NULL,
  res_time TIME NOT NULL,
  guest_lastname VARCHAR(190) NOT NULL,
  party_size INTEGER NOT NULL DEFAULT 2,
  notes TEXT,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  deleted_at DATETIME DEFAULT NULL,
  FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
);
