<?php
/**
 * PDO connection, schema installation and the friendly "not set up yet" screen.
 *
 * Supports SQLite (zero-config default) and MySQL.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg    = CONFIG['db'];
    $driver = strtolower((string)($cfg['driver'] ?? 'sqlite'));

    try {
        if ($driver === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'] ?? 'localhost',
                (int)($cfg['port'] ?? 3306),
                $cfg['name'] ?? '',
                $cfg['charset'] ?? 'utf8mb4'
            );
            $pdo = new PDO($dsn, (string)($cfg['user'] ?? ''), (string)($cfg['pass'] ?? ''), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } else {
            $path = (string)($cfg['path'] ?? (APP_ROOT . '/data/app.sqlite'));
            $dir  = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            $pdo = new PDO('sqlite:' . $path, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA journal_mode = WAL');
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 5000');
        }
    } catch (Throwable $e) {
        error_log('[shagun] DB connection failed (' . $driver . '): ' . $e->getMessage());
        db_setup_required($driver . ' connection failed: ' . $e->getMessage());
    }

    // A reachable database with no tables means the installer has not run.
    // install.php is allowed through so it can create them.
    $isInstaller = isset($_SERVER['SCRIPT_NAME'])
        && basename((string)$_SERVER['SCRIPT_NAME']) === 'install.php';
    if (!$isInstaller) {
        try {
            $pdo->query('SELECT 1 FROM settings LIMIT 1');
        } catch (Throwable $e) {
            error_log('[shagun] schema missing: ' . $e->getMessage());
            db_setup_required('The database exists but its tables do not.');
        }
    }

    return $pdo;
}

/**
 * Print a friendly setup screen and stop.
 */
function db_setup_required(string $detail): void
{
    if (!headers_sent()) {
        http_response_code(503);
        header('Content-Type: text/html; charset=utf-8');
        header('Retry-After: 0');
    }

    $safe = htmlspecialchars($detail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $brand = htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8');

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<meta name="robots" content="noindex">'
        . '<title>Setup required — ' . $brand . '</title>'
        . '<style>'
        . 'body{margin:0;background:#f7f5ef;color:#1a1f1a;font:16px/1.65 -apple-system,Segoe UI,Roboto,sans-serif;}'
        . '.wrap{max-width:620px;margin:0 auto;padding:56px 24px;}'
        . 'h1{font-size:1.7rem;margin:0 0 10px;letter-spacing:-.02em;}'
        . 'ol{padding-left:20px;}li{margin:8px 0;}'
        . 'code{background:#eef2ea;padding:2px 6px;border-radius:5px;font-size:.92em;}'
        . 'a.btn{display:inline-block;margin-top:18px;background:#3a5a40;color:#fff;text-decoration:none;'
        . 'padding:12px 22px;border-radius:999px;font-weight:600;}'
        . '.note{margin-top:26px;font-size:.84rem;color:#6b7a6b;border-top:1px solid #e2ded2;padding-top:16px;}'
        . '</style></head><body><div class="wrap">'
        . '<h1>The site is not connected to its database yet.</h1>'
        . '<p>If you have not set the site up yet, pick one of these two routes:</p>'
        . '<ol>'
        . '<li><strong>Import the SQL file</strong> — in phpMyAdmin select your database, open the Import tab,'
        . ' choose <code>database.sql</code> from the project root and run it. It creates the tables and loads'
        . ' the grades, distributors and settings.</li>'
        . '<li><strong>Or use the installer</strong> — make sure <code>data/</code> is writable by PHP, then open'
        . ' <code>/install.php</code> to create the tables, seed the catalogue and set your own admin login. Delete'
        . ' <code>install.php</code> once it reports success.</li>'
        . '</ol>'
        . '<p>Also check that <code>config.php</code> holds the right database host, name, user and password.</p>'
        . '<a class="btn" href="/install.php">Run the installer</a>'
        . '<p class="note">Detail: ' . $safe . '</p>'
        . '</div></body></html>';
    exit;
}

function db_driver(): string
{
    return strtolower((string)(CONFIG['db']['driver'] ?? 'sqlite'));
}

/**
 * True once the schema has been installed.
 */
function db_installed(): bool
{
    try {
        db()->query('SELECT 1 FROM settings LIMIT 1');
        db()->query('SELECT 1 FROM products LIMIT 1');
        return true;
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Execute a .sql schema file. Handles simple statements; comment lines are
 * stripped and each statement is run on its own.
 */
function db_apply_schema(string $file): int
{
    if (!is_file($file)) {
        throw new RuntimeException('Schema file not found: ' . $file);
    }
    $sql = file_get_contents($file);
    if ($sql === false) {
        throw new RuntimeException('Unable to read schema file');
    }

    $statements = [];
    foreach (preg_split('/;\s*[\r\n]+/', $sql) as $chunk) {
        $lines = preg_split('/\R/', (string)$chunk);
        $clean = [];
        foreach ($lines as $line) {
            if (str_starts_with(ltrim($line), '--')) {
                continue;
            }
            $clean[] = $line;
        }
        $statement = rtrim(trim(implode("\n", $clean)), "; \t\n\r");
        if ($statement !== '') {
            $statements[] = $statement;
        }
    }

    $pdo   = db();
    $count = 0;
    foreach ($statements as $statement) {
        try {
            $pdo->exec($statement);
            $count++;
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            // Re-running an idempotent schema must not fail on "already exists".
            if (stripos($msg, 'already exists') === false && stripos($msg, 'duplicate') === false) {
                throw $e;
            }
        }
    }
    return $count;
}