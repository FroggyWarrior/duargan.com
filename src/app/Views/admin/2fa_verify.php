<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>2FA Verification | Duargan</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="/js/admin.js" defer></script>
</head>
<body>
<div class="admin-login-container">
    <div class="login-card">
        <div class="login-logo">
            <span class="material-icons" style="font-size: 48px; color: var(--primary);">security</span>
        </div>

        <h2 style="text-align: center; margin-bottom: 1rem; color: var(--on-surface);">Two-Step Verification</h2>
        <p style="color: var(--on-surface-variant); text-align: center; margin-bottom: 2rem;">
            Enter the 6-digit code from your authenticator app.
        </p>

        <?php if (isset($_SESSION['2fa_error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['2fa_error']) ?>
            </div>
            <?php unset($_SESSION['2fa_error']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/2fa-verify">
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="material-form-group" style="margin-bottom: 2.5rem;">
                <input type="text" name="2fa_code" class="material-form-input"
                       placeholder=" " required autofocus maxlength="6"
                       pattern="\d{6}" inputmode="numeric" autocomplete="one-time-code">
                <label class="material-form-label">Verification Code</label>
            </div>
            <button type="submit" class="login-btn">Verify</button>
            <a href="/admin/login" style="display: block; text-align: center; margin-top: 1.5rem; color: var(--on-surface-variant); text-decoration: none; font-size: 0.9rem;">Back to login</a>
        </form>
    </div>
</div>
</body>
</html>