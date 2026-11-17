<?php
/**
 * Admin — change the admin username and password.
 */
declare(strict_types=1);
require_once __DIR__ . '/_layout.php';

require_admin();

$error   = null;
$success = null;
$creds   = admin_credentials();

if (is_post()) {
    $input = require_csrf();

    $current = (string)($input['current_password'] ?? '');
    $newUser = trim((string)($input['username'] ?? ''));
    $newPass = (string)($input['new_password'] ?? '');
    $confirm = (string)($input['confirm_password'] ?? '');

    if (!password_verify($current, $creds['hash'])) {
        $error = 'Your current password is not correct.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,32}$/', $newUser)) {
        $error = 'Username must be 3–32 characters: letters, numbers, dot, underscore or dash.';
    } elseif (strlen($newPass) < 10) {
        $error = 'New password must be at least 10 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'The new passwords do not match.';
    } else {
        try {
            $stmt = db()->prepare('UPDATE settings SET v = ? WHERE k = ?');
            $stmt->execute([password_hash($newPass, PASSWORD_DEFAULT), 'admin_pass_hash']);
            $stmt->execute([$newUser, 'admin_user']);

            session_regenerate_id(true);
            $_SESSION['admin'] = ['user' => $newUser, 'at' => time()];
            $success = 'Credentials updated.';
        } catch (Throwable $e) {
            error_log('[shagun] credential update failed: ' . $e->getMessage());
            $error = 'Could not save the new credentials.';
        }
    }
}

$creds = admin_credentials();

admin_header('Account', 'Change the sign-in details for the admin panel');
?>

<?php if ($error): ?>
    <div class="alert alert--error" style="margin-bottom:18px;max-width:520px"><?= e($error) ?></div>
<?php elseif ($success): ?>
    <div class="alert alert--success" style="margin-bottom:18px;max-width:520px"><?= e($success) ?></div>
<?php endif; ?>

<form class="form-card" method="post" action="/admin/account.php" style="max-width:520px">
    <?= csrf_field() ?>
    <h3 style="font-size:1rem">Sign-in details</h3>
    <p class="small muted" style="margin:6px 0 20px">Signed in as <strong><?= e($creds['user']) ?></strong>. Passwords are stored as one-way hashes.</p>

    <div class="form-grid">
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($creds['user']) ?>" required maxlength="32" autocomplete="username">
        </div>
        <div class="field">
            <label for="current_password">Current password</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
        </div>
        <div class="field">
            <label for="new_password">New password</label>
            <input type="password" id="new_password" name="new_password" required minlength="10" autocomplete="new-password" placeholder="At least 10 characters">
        </div>
        <div class="field">
            <label for="confirm_password">Repeat new password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="10" autocomplete="new-password">
        </div>
    </div>

    <div class="form-actions">
        <button class="btn btn-primary" type="submit">Save credentials</button>
        <a class="btn btn-ghost" href="/admin/orders.php">Back to orders</a>
    </div>
</form>

<?php admin_footer(); ?>