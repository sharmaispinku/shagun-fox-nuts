-- Shagun Enterprises — MySQL / MariaDB schema
-- Applied by install.php. Safe to re-run (all statements are IF NOT EXISTS).
-- Indexes are declared inline so re-running never trips on duplicate keys.

CREATE TABLE IF NOT EXISTS settings (
    k VARCHAR(64)  NOT NULL PRIMARY KEY,
    v TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug            VARCHAR(120) NOT NULL,
    name            VARCHAR(160) NOT NULL,
    tagline         VARCHAR(255) NOT NULL DEFAULT '',
    grade_size      VARCHAR(60)  NOT NULL DEFAULT '',
    suta            VARCHAR(40)  NOT NULL DEFAULT '',
    description     TEXT         NULL,
    features        TEXT         NULL,
    moq_kg          INT UNSIGNED NOT NULL DEFAULT 25,
    indicative_rate INT UNSIGNED NOT NULL DEFAULT 0,
    pack_sizes      VARCHAR(255) NOT NULL DEFAULT '',
    image           VARCHAR(160) NOT NULL DEFAULT '',
    badge           VARCHAR(60)  NOT NULL DEFAULT '',
    sort_order      INT          NOT NULL DEFAULT 0,
    active          TINYINT(1)   NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_products_slug (slug),
    KEY idx_products_active (active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS distributors (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(160) NOT NULL,
    area       VARCHAR(160) NOT NULL DEFAULT '',
    since      VARCHAR(40)  NOT NULL DEFAULT '',
    note       VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ref           VARCHAR(32)  NOT NULL,
    type          VARCHAR(24)  NOT NULL DEFAULT 'bulk',
    name          VARCHAR(120) NOT NULL,
    business_name VARCHAR(160) NOT NULL DEFAULT '',
    phone         VARCHAR(32)  NOT NULL,
    email         VARCHAR(160) NOT NULL DEFAULT '',
    city          VARCHAR(120) NOT NULL DEFAULT '',
    state         VARCHAR(120) NOT NULL DEFAULT '',
    grade         VARCHAR(120) NOT NULL DEFAULT '',
    quantity_kg   DECIMAL(12,2) NOT NULL DEFAULT 0,
    packaging     VARCHAR(80)  NOT NULL DEFAULT '',
    timeline      VARCHAR(80)  NOT NULL DEFAULT '',
    gstin         VARCHAR(20)  NOT NULL DEFAULT '',
    message       TEXT         NULL,
    estimate      INT UNSIGNED NOT NULL DEFAULT 0,
    status        VARCHAR(24)  NOT NULL DEFAULT 'new',
    admin_notes   TEXT         NULL,
    ip_hash       VARCHAR(64)  NOT NULL DEFAULT '',
    user_agent    VARCHAR(255) NOT NULL DEFAULT '',
    created_at    DATETIME     NOT NULL,
    updated_at    DATETIME     NOT NULL,
    UNIQUE KEY uniq_orders_ref (ref),
    KEY idx_orders_status (status, created_at),
    KEY idx_orders_phone (phone),
    KEY idx_orders_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS rate_limits (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bucket     VARCHAR(64)  NOT NULL,
    created_at DATETIME     NOT NULL,
    KEY idx_rate_bucket (bucket, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;