# 🖼️ Presentation Layer (Views Documentation)

The View layer consists of plain PHP templates. Controllers pass data to these files as associative arrays, which are then transformed into local variables using PHP's `extract()` function during the rendering process.

## 📂 Detailed Directory Structure

### 🌐 Public Views
These templates define the public-facing look and feel of the artist's portfolio.

- **`home.php`**: The entry point. Displays the site-wide announcement (if active), the latest official release in a high-impact hero section, and a grid for previous tracks.
- **`music.php`**: The full discography catalog. Implements a sophisticated client-side filtering system using JavaScript and HTML `data-*` attributes for genre and release type filtering without page reloads.
- **`song.php`**: The track detail page. Features high-res cover art, release metadata, dynamic platform links with branding colors, and a "More Music" suggestion grid.
- **`about.php`**: Biographical page featuring artist photography and a personal story layout.
- **`contact.php`**: Direct contact information and Discord community integration.

### 🔒 Administrative Views (`admin/`)
Organized by resource type, these templates handle the backend management interface.

- **`panel.php`**: The primary dashboard for managing music. Shows a visual grid of all tracks with status indicators and quick-action buttons (Edit/Delete).
- **`login.php` & `2fa_verify.php`**: Secure multi-step authentication templates using Material Design 3 input states and CSRF protection.
- **`song_form.php`**: A unified template for both creating and editing songs. It handles conditional logic for local file uploads versus remote cover URLs and dynamically displays URL inputs for streaming platforms.
- **`credentials/index.php`**: Interface for updating the administrator profile and managing TOTP (2FA) settings, including binary QR code rendering.
- **Resource Templates** (`genres/`, `platforms/`, `song_types/`, `social_media/`): Standardized templates for metadata management. These include usage counting to prevent accidental deletion of categories currently assigned to tracks.
- **`announcement/`**: Special views for the site-wide banner, featuring a live preview system that updates as the admin types.

### 🧩 Reusable Components (`partials/`)
Shared snippets that ensure consistency across the entire application.

- **`header.php`**: Renders the `<head>` section, dynamic SEO Open Graph tags, the primary navigation pills, and the global theme switcher.
- **`footer.php`**: Standard site footer containing dynamic social links, copyright information, and licensing details.
- **`admin_sidebar.php`**: The side-navigation drawer for the dashboard. It manages active-state highlighting to show the admin their current location within the CMS.

## 🎨 Rendering System

The `BaseController` uses a "Wrapper" approach:
1. **Header Inclusion**: Includes `header.php`, making common data like social links available globally.
2. **Variable Extraction**: The `$data` array from the Controller is extracted into the local scope.
3. **View Inclusion**: The specific page view (e.g., `music.php`) is included.
4. **Footer Inclusion**: Includes `footer.php` and closes the HTML tags.

## 💡 Frontend Logic & UI Patterns

### Material Design 3 (MD3) Implementation
The UI follows MD3 principles for a modern, tactile feel:
- **Theming**: Supports Light and Dark modes. State is persisted in `localStorage` and applied via a `data-theme` attribute on the `<html>` root to prevent a flash of unstyled content.
- **Color Tokens**: CSS variables (tokens) are used for all colors, allowing for easy global branding changes.
- **Elevation & State**: Cards and buttons use subtle shadows and state layers (hover/pressed) to provide visual feedback.

### Interaction Strategies
- **Stretched Link Pattern**: Used in music cards. A pseudo-element covers the entire card to make it clickable, while platform-specific icons are placed on a higher `z-index` so they can still be clicked independently.
- **Responsive Drawer**: The admin sidebar automatically collapses into a burger menu on mobile devices, using a backdrop-blur overlay for depth.
- **Filter Logic**: The music library uses JavaScript to scan `data-genres` and `data-type` attributes on HTML elements for real-time, zero-latency filtering.

### Performance Optimization
- **Image Handling**: Uses native `loading="lazy"` for all grid images.
- **Intersection Observer**: The "Fade-in on scroll" effect uses the Intersection Observer API instead of scroll listeners for better scroll performance.

## 🛡 Security in Views
### 1. XSS Prevention
All dynamic output is passed through `htmlspecialchars()` by default. 

### 2. Trusted SVG Rendering
Because the CMS allows raw SVG code for icons, we use `html_entity_decode()` for icons. This is safe because the `SvgSanitizer` utility strips all `<script>` tags and event handlers (like `onclick`) before the data ever reaches the database.

### 3. CSRF Protection
All administrative forms include a hidden token:
```html
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
```
This is verified by the `BaseAdminController` on every `POST` request.

### 4. Navigation & Directory Traversal
The `BaseController` validates the `$view` path string before inclusion to ensure that only authorized template files within the Views directory can be loaded.