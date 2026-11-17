-- Shagun Enterprises — SQLite schema (zero-config default)
-- Applied by install.php. Safe to re-run.

CREATE TABLE IF NOT EXISTS settings (
    k TEXT PRIMARY KEY,
    v TEXT
);

CREATE TABLE IF NOT EXISTS products (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    slug            TEXT NOT NULL UNIQUE,
    name            TEXT NOT NULL,
    tagline         TEXT NOT NULL DEFAULT '',
    grade_size      TEXT NOT NULL DEFAULT '',
    suta            TEXT NOT NULL DEFAULT '',
    description     TEXT NOT NULL DEFAULT '',
    features        TEXT NOT NULL DEFAULT '[]',
    moq_kg          INTEGER NOT NULL DEFAULT 25,
    indicative_rate INTEGER NOT NULL DEFAULT 0,
    pack_sizes      TEXT NOT NULL DEFAULT '',
    image           TEXT NOT NULL DEFAULT '',
    badge           TEXT NOT NULL DEFAULT '',
    sort_order      INTEGER NOT NULL DEFAULT 0,
    active          INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS distributors (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    area       TEXT NOT NULL DEFAULT '',
    since      TEXT NOT NULL DEFAULT '',
    note       TEXT NOT NULL DEFAULT '',
    sort_order INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS orders (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    ref           TEXT NOT NULL UNIQUE,
    type          TEXT NOT NULL DEFAULT 'bulk',
    name          TEXT NOT NULL,
    business_name TEXT NOT NULL DEFAULT '',
    phone         TEXT NOT NULL,
    email         TEXT NOT NULL DEFAULT '',
    city          TEXT NOT NULL DEFAULT '',
    state         TEXT NOT NULL DEFAULT '',
    grade         TEXT NOT NULL DEFAULT '',
    quantity_kg   REAL NOT NULL DEFAULT 0,
    packaging     TEXT NOT NULL DEFAULT '',
    timeline      TEXT NOT NULL DEFAULT '',
    gstin         TEXT NOT NULL DEFAULT '',
    message       TEXT NOT NULL DEFAULT '',
    estimate      INTEGER NOT NULL DEFAULT 0,
    status        TEXT NOT NULL DEFAULT 'new',
    admin_notes   TEXT NOT NULL DEFAULT '',
    ip_hash       TEXT NOT NULL DEFAULT '',
    user_agent    TEXT NOT NULL DEFAULT '',
    created_at    TEXT NOT NULL,
    updated_at    TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_orders_status  ON orders (status, created_at);
CREATE INDEX IF NOT EXISTS idx_orders_phone   ON orders (phone);
CREATE INDEX IF NOT EXISTS idx_orders_created ON orders (created_at);

CREATE TABLE IF NOT EXISTS rate_limits (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    bucket     TEXT NOT NULL,
    created_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_rate_bucket ON rate_limits (bucket, created_at);