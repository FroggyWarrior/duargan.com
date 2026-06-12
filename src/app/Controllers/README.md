# 🎮 Controllers Layer

Controllers act as the intermediary between the user input and the application logic. This project uses a hierarchical structure to separate public concerns from administrative ones.

## 🏗 Hierarchy

1. **`Core\Controller`**: Basic rendering logic.
2. **`BaseController`**: Inherited by all public pages.
    - Initializes common data (Social Media links, default SEO tags).
    - Sets up the `csrf_token` for the session.
3. **`Admin\BaseAdminController`**: The security gateway for the dashboard.
    - **Authentication Check:** Redirects to `/admin/login` if the session is not active.
    - **CSRF Protection:** Automatically validates the `csrf_token` on every `POST` request.

## 📂 Controller Classes

### 🌐 `Core\Controller.php`
This is the foundational abstract class from which all other controllers inherit. It provides basic, reusable functionalities.
- **Class**: `Controller`
    - **Purpose**: Serves as the abstract base for all controllers, providing fundamental functionalities like rendering views and redirection.
- **Methods**:
    - `render(string $view, array $data = [])`:
        - **Purpose**: Loads a specified view file and makes provided data available to it.
        - **Logic**: Uses `extract($data)` to transform keys of the `$data` array into local variables within the scope of the included view file. It constructs the full path to the view (`../Views/{$view}.php`) and includes it. If the view file is not found, it terminates execution with an error message.
    - `redirect(string $url)`:
        - **Purpose**: Redirects the user's browser to a specified URL.
        - **Logic**: Sets the `Location` HTTP header to the given URL and then calls `exit` to ensure no further script execution, preventing potential issues after redirection.

### 🌐 `BaseController.php`
Extends `Core\Controller` and provides common setup for all public-facing pages.
- **Class**: `BaseController`
    - **Purpose**: Initializes shared data (like social media links, SEO metadata) and sets up CSRF tokens for forms, which are then available to all public views.
- **Attributes**:
    - `protected array $commonData`: An associative array holding data consistently needed across public views (e.g., social media links, default page title, description, keywords).
- **Methods**:
    - `__construct()`:
        - **Purpose**: Initializes the controller, fetching common data and setting up security tokens.
        - **Logic**: Instantiates `SocialMediaModel` to retrieve active social media platforms. Sets default SEO metadata (`page_title`, `page_description`, `meta_keywords`). Initializes a `csrf_token` in the session if one doesn't exist, adding it to `$commonData`.
    - `render(string $view, array $data = [], bool $showFooter = true)`:
        - **Purpose**: Overrides the parent `render` method to wrap views with common header and footer partials and merge `$commonData`.
        - **Logic**: Includes a security check (`preg_match`) on the `$view` path to prevent directory traversal attacks. Merges `$commonData` with the `$data` specific to the current view. It then includes `Views/partials/header.php`, the specified `$view.php`, and optionally `Views/partials/footer.php`.

### 🌐 `PageController.php`
Manages the public-facing pages of the website.
- **Class**: `PageController`
    - **Purpose**: Handles the display logic for the home page, music catalog, individual song details, about, and contact pages.
- **Methods**:
    - `index()`:
        - **Purpose**: Displays the home page with the latest official releases and announcements.
        - **Logic**: Fetches active announcements using `AnnouncementModel` and official song releases using `SongModel`. It identifies the single latest release and passes all relevant data to the `home` view.
    - `song()`:
        - **Purpose**: Displays the detail page for a specific song.
        - **Logic**: Retrieves a song by its ID from `SongModel`. If the song is not found, it redirects to the `/music` page. It also fetches other related songs and dynamically generates social sharing buttons. Crucially, it sets specific SEO metadata (`pageTitle`, `pageDescription`, `pageImage`) for the individual song to enhance social sharing and search engine visibility.
    - `generateShareButtons(array $song)`:
        - **Purpose**: A private helper method to generate the HTML for social sharing buttons for a given song.
        - **Logic**: Constructs URLs for sharing on platforms like X (Twitter), Facebook, and via email, along with a "Copy Link" button. It URL-encodes song details for safe inclusion in share links.
    - `music()`:
        - **Purpose**: Displays the music library page, listing all available songs.
        - **Logic**: Fetches all active genres, song types, and all music tracks from `SongModel` to populate the filtering and display options on the `music` view.
    - `about()`:
        - **Purpose**: Displays the "About Me" page.
        - **Logic**: Renders the `about` view with a specific page title.
    - `contact()`:
        - **Purpose**: Displays the "Contact" page.
        - **Logic**: Renders the `contact` view with a specific page title, explicitly passing `false` to the `render` method to omit the standard footer, as this page has its own social links.

### 🔒 `Admin\BaseAdminController.php`
The security gateway for all admin-panel controllers.
- **Class**: `BaseAdminController`
    - **Purpose**: Enforces authentication and CSRF protection for all administrative actions, ensuring only authorized and legitimate requests are processed.
- **Methods**:
    - `__construct()`:
        - **Purpose**: Executes security checks upon instantiation of any admin controller.
        - **Logic**:
            1.  **Authentication Check**: Verifies if `$_SESSION['admin_logged_in']` is set and true. If not, and the current request path is not the login or 2FA verification page, it redirects the user to `/admin/login`.
            2.  **CSRF Token Initialization**: Ensures a `csrf_token` is present in the session, generating a new one if missing.
            3.  **CSRF Protection**: For all `POST` requests, it compares the `csrf_token` from the `$_POST` data with the one stored in `$_SESSION`. If they do not match, it terminates the script with a "CSRF token validation failed" error, preventing cross-site request forgery attacks.

### 🔒 `Admin\DashboardController.php`
The primary controller for managing music tracks in the admin panel.
- **Class**: `DashboardController`
    - **Purpose**: Handles CRUD (Create, Read, Update, Delete) operations for songs, including complex tasks like image uploads, processing, and managing many-to-many relationships with genres and platforms.
- **Attributes**:
    - `private AdminSongModel $songModel`: An instance of `AdminSongModel` specifically for administrative database operations related to songs.
- **Methods**:
    - `__construct()`:
        - **Purpose**: Initializes the controller and its dependencies.
        - **Logic**: Calls the parent `BaseAdminController` constructor for authentication and then initializes `$songModel`.
    - `index()`:
        - **Purpose**: Displays a list of all songs in the admin panel.
        - **Logic**: Retrieves all music tracks using `$this->songModel->getAllMusic()` and renders the `admin/panel` view.
    - `create()`:
        - **Purpose**: Displays the form for creating a new song.
        - **Logic**: Fetches all active genres, platforms, and song types from their respective models to populate the form's selection fields. Renders the `admin/song_form` view.
    - `store()`:
        - **Purpose**: Processes the submission of the new song creation form.
        - **Logic**:
            1.  **Input Validation**: Checks for potential `post_max_size` overflow and then validates all submitted song data (title, release date, type, genres, cover image).
            2.  **Image Handling**: Calls `handleCoverUpload()` to manage the upload, resizing, and conversion of the cover image.
            3.  **Database Insertion**: If validation passes, it creates the song record in the database using `$this->songModel->create()`.
            4.  **Relationship Syncing**: Synchronizes the many-to-many relationships for genres and platforms using `$this->songModel->syncGenres()` and `$this->songModel->syncPlatforms()`.
            5.  **Feedback & Redirect**: Stores success or error messages in the session and redirects to the admin panel.
    - `edit(int $id)`:
        - **Purpose**: Displays the form for editing an existing song.
        - **Logic**: Retrieves the song by its ID. If not found, it redirects with an error. It then fetches all active genres, platforms, and song types, along with the specific genres and platform URLs associated with the song, to pre-fill the edit form. Renders `admin/song_form`.
    - `update(int $id)`:
        - **Purpose**: Processes the submission of the song update form.
        - **Logic**: Similar to `store()`, but for updating an existing record. It includes logic to delete the old cover image file if a new one is uploaded or a new URL is provided.
    - `delete(int $id)`:
        - **Purpose**: Deletes a song record from the database.
        - **Logic**: Fetches the song's data to retrieve its `cover_image_url`. After deleting the song from the database, it calls `deleteLocalFile()` to remove the associated image file from the server.
    - `deleteLocalFile(string $path)`:
        - **Purpose**: A private helper method to safely delete a local file from the server.
        - **Logic**: Checks if the provided path is local (not an external URL) and if the file exists, then uses `unlink()` to remove it. Includes error logging for failed deletions.
    - `validateSongData(string $title, string $releaseDate, int $typeId, array $genres, ?string $cover, bool $isEdit)`:
        - **Purpose**: A private helper method to validate the song data submitted via a form.
        - **Logic**: Checks for the presence and correct format of required fields (title, release date, song type, genres). For new songs, it ensures that a cover image (either uploaded file or URL) is provided. Returns an array of validation error messages.
    - `handleCoverUpload()`:
        - **Purpose**: A private helper method to manage the upload and initial processing of cover image files.
        - **Logic**: Handles PHP upload errors, creates the upload directory if necessary, checks for write permissions, and validates the file type. It then calls `processImage()` to resize and convert the image to WebP (or JPG as a fallback). Returns the relative path to the saved image or `null` on failure.
    - `processImage(string $sourcePath, string $destinationPath, string $format = 'webp', int $maxDim = 900, int $quality = 90)`:
        - **Purpose**: A private helper method that resizes, compresses, and converts an image using the PHP GD library.
        - **Logic**: Checks for the GD extension. Loads the image based on its type, calculates new dimensions to fit within `$maxDim` while maintaining aspect ratio, handles transparency for PNG/WebP, and saves the processed image to the destination path. Sets file permissions and cleans up image resources.

### 🔒 `Admin\AuthController.php`
Manages the administrator authentication process.
- **Class**: `AuthController`
    - **Purpose**: Handles login, Two-Factor Authentication (2FA) verification, and logout for the admin panel.
- **Attributes**:
    - `private AdminModel $adminModel`: An instance of `AdminModel` for interacting with admin credentials in the database.
- **Methods**:
    - `__construct()`:
        - **Purpose**: Initializes the controller.
        - **Logic**: Instantiates `AdminModel` and ensures a CSRF token is present in the session.
    - `login()`:
        - **Purpose**: Displays the administrator login form.
        - **Logic**: If an admin is already logged in (`$_SESSION['admin_logged_in']` is true), it redirects to the admin panel. Otherwise, it renders the `admin/login` view.
    - `doLogin()`:
        - **Purpose**: Processes the administrator login attempt.
        - **Logic**: Performs CSRF verification. Retrieves admin data by username and verifies the password using `password_verify()`. If 2FA is enabled, it stores the admin ID in the session and redirects to the 2FA verification page. Otherwise, it regenerates the session ID, sets `$_SESSION['admin_logged_in']` to true, and redirects to the admin panel. Includes a `sleep(2)` delay on failed attempts to mitigate brute-force attacks.
    - `verify2fa()`:
        - **Purpose**: Displays the 2FA verification form.
        - **Logic**: If `$_SESSION['2fa_admin_id']` is not set (meaning the user hasn't passed the initial login step), it redirects to the login page. Otherwise, it renders the `admin/2fa_verify` view.
    - `doVerify2fa()`:
        - **Purpose**: Processes the 2FA verification code submitted by the user.
        - **Logic**: Ensures the `$_SESSION['2fa_admin_id']` is set and performs CSRF verification. Retrieves the admin's decrypted 2FA secret and uses `TOTPAuthenticator::verifyCode()` to validate the provided code. On success, it clears the 2FA session variable, regenerates the session ID, sets `$_SESSION['admin_logged_in']` to true, and redirects to the admin panel. Includes a `sleep(1)` delay on failed attempts.
    - `logout()`:
        - **Purpose**: Logs out the administrator.
        - **Logic**: Clears all session variables (`$_SESSION = []`), invalidates the session cookie, destroys the session data on the server, and redirects the user to the login page.

### 🔒 `Admin\CredentialsController.php`
Manages admin credentials and Two-Factor Authentication (2FA) settings.
- **Class**: `CredentialsController`
    - **Purpose**: Allows administrators to update their username and password, and to enable or disable Two-Factor Authentication.
- **Attributes**:
    - `private AdminModel $adminModel`: An instance of `AdminModel` for managing admin credentials.
- **Methods**:
    - `__construct()`:
        - **Purpose**: Initializes the controller.
        - **Logic**: Calls the parent `BaseAdminController` constructor and initializes `$adminModel`.
    - `index()`:
        - **Purpose**: Displays the main credentials management page.
        - **Logic**: Fetches the current admin's data. If a `setup_2fa` GET parameter is present and 2FA is not enabled, it generates a new 2FA secret and an `otpauthUrl` (for QR code display) using `TOTPAuthenticator`. Renders the `admin/credentials/index` view.
    - `updateCredentials()`:
        - **Purpose**: Processes the update of the administrator's username and/or password.
        - **Logic**: Verifies the provided current username and password. Validates that new passwords match if provided. Calls `$this->adminModel->updateCredentials()` to update the encrypted username and/or hashed password in the database. Sets feedback messages and redirects.
    - `enable2fa()`:
        - **Purpose**: Activates Two-Factor Authentication for the administrator.
        - **Logic**: Verifies the provided 2FA code against the generated secret using `TOTPAuthenticator::verifyCode()`. If valid, it calls `$this->adminModel->enable2fa()` to store the encrypted secret in the database. Sets feedback and redirects.
    - `disable2fa()`:
        - **Purpose**: Deactivates Two-Factor Authentication for the administrator.
        - **Logic**: Calls `$this->adminModel->disable2fa()` to clear the 2FA secret and disable the 2FA flag in the database. Sets feedback and redirects.

### 🔒 `Admin\SongTypesController.php`
Manages administrative operations for song types.
- **Class**: `SongTypesController`
    - **Purpose**: Provides CRUD functionality for managing different categories of songs (e.g., "Official Release", "Remix").
- **Attributes**:
    - `private SongTypeModel $typeModel`: An instance of `SongTypeModel` for database interactions.
- **Methods**:
    - `__construct()`: Initializes the parent `BaseAdminController` and `$typeModel` with admin privileges.
    - `index()`: Fetches all song types along with their usage counts and renders `admin/song_types/index`.
    - `create()`: Displays the form for creating a new song type.
    - `store()`: Validates input (`name`, `slug`), generates a slug if needed, and calls `$this->typeModel->create()`. Handles duplicate entry errors.
    - `edit(int $id)`: Fetches a song type by ID and renders the edit form.
    - `update(int $id)`: Validates input and calls `$this->typeModel->update()`. Handles duplicate entry errors.
    - `delete(int $id)`: Checks if the song type is used by any songs. If so, it deactivates it; otherwise, it performs a hard delete.
    - `toggle(int $id)`: Toggles the `is_active` status of a song type.

### 🔒 `Admin\GenresController.php`
Manages administrative operations for music genres.
- **Class**: `GenresController`
    - **Purpose**: Provides CRUD functionality for managing music genres (e.g., "Electronic", "Lo-fi").
- **Attributes**:
    - `private GenreModel $genreModel`: An instance of `GenreModel` for database interactions.
- **Methods**:
    - `__construct()`: Initializes the parent `BaseAdminController` and `$genreModel` with admin privileges.
    - `index()`: Fetches all genres along with their usage counts and renders `admin/genres/index`.
    - `create()`: Displays the form for creating a new genre.
    - `store()`: Validates input (`name`, `slug`), generates a slug if needed, and calls `$this->genreModel->create()`. Handles duplicate entry errors.
    - `edit(int $id)`: Fetches a genre by ID and renders the edit form.
    - `update(int $id)`: Validates input and calls `$this->genreModel->update()`. Handles duplicate entry errors.
    - `delete(int $id)`: Checks if the genre is used by any songs. If so, it deactivates it; otherwise, it performs a hard delete.
    - `toggle(int $id)`: Toggles the `is_active` status of a genre.

### 🔒 `Admin\PlatformsController.php`
Manages administrative operations for music platforms.
- **Class**: `PlatformsController`
    - **Purpose**: Provides CRUD functionality for managing music streaming and purchase platforms (e.g., Spotify, YouTube).
- **Attributes**:
    - `private PlatformModel $platformModel`: An instance of `PlatformModel` for database interactions.
- **Methods**:
    - `__construct()`: Initializes the parent `BaseAdminController` and `$platformModel` with admin privileges.
    - `index()`: Fetches all platforms along with their usage counts and renders `admin/platforms/index`.
    - `create()`: Displays the form for creating a new platform.
    - `store()`: Validates input (`name`, `slug`, `base_url`, `icon_svg`, `color`, `display_order`). Sanitizes the `icon_svg` using `SvgSanitizer`. Calls `$this->platformModel->create()`. Handles duplicate entry errors.
    - `edit(int $id)`: Fetches a platform by ID and renders the edit form.
    - `update(int $id)`: Validates input. Sanitizes the `icon_svg` using `SvgSanitizer`. Calls `$this->platformModel->update()`. Handles duplicate entry errors.
    - `delete(int $id)`: Checks if the platform is used by any songs. If so, it deactivates it; otherwise, it performs a hard delete.
    - `toggle(int $id)`: Toggles the `is_active` status of a platform.

### 🔒 `Admin\SocialMediaController.php`
Manages administrative operations for social media platforms.
- **Class**: `SocialMediaController`
    - **Purpose**: Provides CRUD functionality for managing social media links displayed on the website (e.g., Instagram, Discord).
- **Attributes**:
    - `private SocialMediaModel $socialModel`: An instance of `SocialMediaModel` for database interactions.
- **Methods**:
    - `__construct()`: Initializes the parent `BaseAdminController` and `$socialModel` with admin privileges.
    - `index()`: Fetches all social media platforms and renders `admin/social_media/index`.
    - `create()`: Displays the form for creating a new social media platform.
    - `store()`: Validates input (`name`, `slug`, `base_url`, `icon_svg`, `display_order`). Sanitizes the `icon_svg` using `SvgSanitizer`. Calls `$this->socialModel->create()`. Handles duplicate entry errors.
    - `edit(int $id)`: Fetches a social media platform by ID and renders the edit form.
    - `update(int $id)`: Validates input. Sanitizes the `icon_svg` using `SvgSanitizer`. Calls `$this->socialModel->update()`. Handles duplicate entry errors.
    - `delete(int $id)`: Deletes a social media platform.
    - `toggle(int $id)`: Toggles the `is_active` status of a social media platform.

### 🔒 `Admin\AnnouncementController.php`
Manages the site-wide announcement banner.
- **Class**: `AnnouncementController`
    - **Purpose**: Provides functionality to create, edit, and toggle the visibility of a single, site-wide announcement.
- **Attributes**:
    - `private AnnouncementModel $announcementModel`: An instance of `AnnouncementModel` for database interactions.
- **Methods**:
    - `__construct()`: Initializes the parent `BaseAdminController` and `$announcementModel` with admin privileges.
    - `index()`: Fetches the current announcement and determines if it's configured. Renders `admin/announcement/index`.
    - `edit()`: Fetches the current announcement data and renders `admin/announcement/form` for editing.
    - `update()`: Validates input (`title`, `backgroundColor`, `text`). Calls `$this->announcementModel->save()` to either create or update the announcement record (which is a singleton with `id=1`). Sets feedback and redirects.
    - `toggle()`: Calls `$this->announcementModel->toggle()` to switch the active status of the announcement. Sets feedback and redirects.

## 🛡 Security Logic
All administrative actions (Store, Update, Delete) are protected by a manual CSRF check in the `BaseAdminController` constructor. This ensures that any form submitted to an admin route must contain a token matching the one stored in the user's session.

Additionally, input validation is performed in each controller's `store()` and `update()` methods to prevent common vulnerabilities like SQL injection (though PDO prepared statements are the primary defense) and XSS (especially for SVG content). Image uploads are also rigorously validated and processed to prevent file upload vulnerabilities.