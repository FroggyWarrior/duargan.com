<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login | Duargan</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
<div class="admin-login-container">
    <div class="login-card">
        <div class="login-logo">
            <img src="/img/logo.svg" alt="Duargan Logo">
        </div>

        <?php if (isset($_SESSION['login_error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_SESSION['login_error']) ?>
            </div>
            <?php unset($_SESSION['login_error']); ?>
        <?php endif; ?>

        <form method="POST" action="/admin/login">
            <!-- CSRF Protection -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div class="material-form-group">
                <input type="text" name="username" id="username" class="material-form-input" placeholder=" " required autofocus>
                <label class="material-form-label" for="username">Username</label>
            </div>

            <div class="material-form-group">
                <input type="password" name="password" id="password" class="material-form-input" placeholder=" " required>
                <label class="material-form-label" for="password">Password</label>
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="login-theme-toggle">
            <div class="theme-toggle" id="themeToggle">
                <span class="material-icons">light_mode</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Theme toggle
    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);

    const themeToggle = document.getElementById('themeToggle');
    const updateIcon = () => {
        const icon = themeToggle.querySelector('.material-icons');
        const theme = document.documentElement.getAttribute('data-theme');
        icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
    };
    updateIcon();

    themeToggle.addEventListener('click', () => {
        let theme = document.documentElement.getAttribute('data-theme');
        let next = theme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateIcon();
    });
</script>
</body>
</html>