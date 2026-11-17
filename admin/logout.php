<?php
/**
 * Admin — destroy the session.
 */
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

admin_logout();

header('Location: /admin/', true, 302);
exit;