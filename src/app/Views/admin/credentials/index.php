<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Manage Credentials | Duargan Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
    <script src="/js/admin.js" defer></script>
    <script src="/js/qrcode.min.js"></script>
</head>
<body>
<?php
$currentAdminPage = 'credentials';
include __DIR__ . '/../../partials/admin_sidebar.php';
renderAdminSidebar($currentAdminPage);
?>
<div class="admin-page-wrapper">
    <main class="admin-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="admin-header-bar">
            <h1 class="admin-title">Admin Credentials</h1>
        </div>

        <div class="form-card" style="margin-bottom: 2rem;">
            <div class="form-section">
                <h3>Two-Factor Authentication (2FA)</h3>
                <p class="form-help">Add an extra layer of security. Use any TOTP app like Authy, Google Authenticator, or Aegis.</p>

                <?php if ($admin['2fa_enabled']): ?>
                    <div class="info-box" style="background-color: rgba(76, 175, 80, 0.1); border: 1px solid rgba(76, 175, 80, 0.3); border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                        <p style="color: #4CAF50; margin: 0; font-weight: 500; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="material-icons">check_circle</span>
                            2FA is currently ENABLED
                        </p>
                    </div>
                    <form method="POST" action="/admin/credentials/disable2fa" class="confirm-form" 
                          data-confirm-title="Disable 2FA" 
                          data-confirm-message="Are you sure you want to disable Two-Factor Authentication? Your account will be less secure." 
                          data-confirm-btn="Disable">
                        <!-- CSRF Protection -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" class="btn btn-delete" style="padding: 0.75rem 1.5rem;">
                            <span class="material-icons">no_encryption</span>
                            Disable 2FA
                        </button>
                    </form>
                    <br>
                <?php elseif ($show2faSetup): ?>
                    <div class="setup-2fa-container" style="text-align: center; padding: 1rem; border: 1px dashed var(--outline); border-radius: 12px;">
                        <h4>Set up Authenticator App</h4>
                        <p class="form-help">Scan this QR code with your 2FA app</p>
                        <div id="qrcode" data-otpauth="<?= $otpauthUrl ?>" style="display: flex; justify-content: center; margin: 1.5rem 0; padding: 10px; background: white; width: fit-content; margin-left: auto; margin-right: auto; border-radius: 8px;"></div>
                        <p style="font-family: monospace; background: var(--surface); padding: 0.5rem; border-radius: 4px;"><?= $new2faSecret ?></p>

                        <form method="POST" action="/admin/credentials/enable2fa" style="max-width: 300px; margin: 1.5rem auto;">
                            <!-- CSRF Protection -->
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <input type="hidden" name="2fa_secret" value="<?= htmlspecialchars($new2faSecret) ?>">
                            <div class="material-form-group">
                                <input type="text" id="2fa_code" name="2fa_code" class="material-form-input" placeholder=" " required maxlength="6" pattern="\d{6}" inputmode="numeric">
                                <label class="material-form-label" for="2fa_code">Verification Code</label>
                            </div>
                            <div class="form-actions" style="justify-content: center; border: none; margin-top: 0;">
                                <button type="submit" class="btn btn-submit">Verify and Enable</button>
                                <a href="/admin/credentials" class="btn btn-cancel">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="info-box" style="background-color: rgba(255, 152, 0, 0.1); border: 1px solid rgba(255, 152, 0, 0.3); border-radius: 8px; padding: 1rem; margin: 1rem 0;">
                        <p style="color: #ff9800; margin: 0; font-weight: 500;">2FA is currently DISABLED.</p>
                    </div>
                    <a href="/admin/credentials?setup_2fa=1" class="btn btn-submit" style="padding: 0.75rem 1.5rem;">
                        <span class="material-icons">qr_code_2</span>
                        Setup 2FA
                    </a>
                    <br>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-card">
            <div class="info-box" style="background-color: rgba(33, 150, 243, 0.1); border: 1px solid rgba(33, 150, 243, 0.3); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                <h4 style="color: #2196F3; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-icons">info</span>
                    Current Username
                </h4>
                <p style="margin: 0; color: var(--on-surface); font-weight: 500;"><?= htmlspecialchars($admin['username']) ?></p>
            </div>

            <div class="warning-box">
                <h4><span class="material-icons">security</span> Security Notice</h4>
                <p>Your password is securely hashed. Changing your credentials will affect your ability to log in. Make sure to remember your new credentials.</p>
            </div>

            <form method="POST" action="/admin/credentials/update">
                <!-- CSRF Protection -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-section">
                    <h3>Current Credentials</h3>
                    <p class="form-help">Enter your current username and password to verify your identity</p>

                    <div class="material-form-group with-help">
                        <input type="text" id="current_username" name="current_username" class="material-form-input" placeholder=" " required>
                        <label class="material-form-label" for="current_username">Current Username *</label>
                    </div>
                    <p class="form-help">Your current admin username</p>

                    <div class="material-form-group with-help">
                        <input type="password" id="current_password" name="current_password" class="material-form-input" placeholder=" " required>
                        <label class="material-form-label" for="current_password">Current Password *</label>
                    </div>
                    <p class="form-help">Your current admin password</p>
                </div>

                <div class="form-section">
                    <h3>New Credentials</h3>
                    <p class="form-help">Change either username, password, or both. Leave fields blank if you don't want to change them.</p>

                    <div class="material-form-group with-help">
                        <input type="text" id="new_username" name="new_username" class="material-form-input" placeholder=" ">
                        <label class="material-form-label" for="new_username">New Username</label>
                    </div>
                    <p class="form-help">Leave blank to keep current username</p>

                    <div class="material-form-group with-help">
                        <input type="password" id="new_password" name="new_password" class="material-form-input" placeholder=" ">
                        <label class="material-form-label" for="new_password">New Password</label>
                    </div>
                    <p class="form-help">Leave blank to keep current password</p>

                    <div class="material-form-group with-help">
                        <input type="password" id="confirm_password" name="confirm_password" class="material-form-input" placeholder=" ">
                        <label class="material-form-label" for="confirm_password">Confirm New Password</label>
                    </div>
                    <p class="form-help">Required if changing password</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-submit">
                        <span class="material-icons">lock_reset</span>
                        Update Credentials
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>