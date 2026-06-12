# 🌐 Public Entry Point & Assets

This directory is the **Document Root** of the application. For security and architectural reasons, this is the only directory that should be accessible via a web browser. It contains the Front Controller and all static assets (CSS, JS, and Images).

## 📄 Key Components

### 🚀 `index.php` (The Front Controller)
This is the heartbeat of the application. Every request that comes to the site is funneled here.
- **Autoloading:** It defines a basic SPL autoloader that maps the `App\` namespace to the `../app/` directory, allowing classes to be loaded dynamically without manual `include` statements.
- **Routing:** It initializes the `Core\Router` and defines every valid URL for the website and the admin panel, mapping them to specific Controller methods.
- **Environment:** It starts the PHP session and configures basic error logging.

### 🛠 Server Configuration & Security
- **`.htaccess`**: Used during development to manage URL rewriting (removing `.php` extensions and supporting clean routes).
- **`.htaccess.production`**: A security-hardened version designed for deployment. It enforces HTTPS and sets strict **Security Headers**:
    - **CSP (Content Security Policy):** Prevents XSS by restricting where scripts and styles can be loaded from.
    - **HSTS:** Forces browsers to use secure connections.
    - **X-Frame-Options:** Protects against clickjacking.
- **`php.ini`**: Overrides default PHP settings to allow for larger file uploads (`20MB`), which is necessary for high-quality music cover art.

## 📂 Asset Structure

### 🎨 `css/`
- **`styles.css`**: The main stylesheet for the public site. It uses Material Design 3 design tokens (CSS variables) for easy theming and dark mode support.
- **`admin.css`**: Contains styles specific to the administrative dashboard, including the responsive navigation drawer and form layouts.

### ⚡ `js/`
- **`script.js`**: Handles public-facing interactivity, such as the zero-latency music filtering logic and scroll-reveal animations using the `IntersectionObserver` API.
- **`admin.js`**: Powering the CMS dashboard. It handles live previews for SVG icons, real-time color syncing for the announcement banner, and 2FA QR code rendering.
- **`qrcode.min.js`**: A utility library used in the admin panel to generate setup codes for authenticator apps.

### 🖼 `img/`
- Contains static assets like the site logo and artist photography.
- **`covers/`**: This directory is used by the `DashboardController` to store processed WebP cover art for every track. 
    > **Note:** On production servers, this directory must have write permissions for the web server user (e.g., `www-data`).

## 🔄 Typical Request Lifecycle
1. A user enters `yourdomain.com/music`.
2. Apache (via `.htaccess`) detects that `/music` is not a physical file and sends the request to `index.php`.
3. `index.php` bootstraps the `Router`.
4. The `Router` matches `/music` to `PageController::music()`.
5. The Controller fetches data from a Model and renders a View.
6. The View generates HTML that references assets (CSS/JS) located here in the `public/` folder.