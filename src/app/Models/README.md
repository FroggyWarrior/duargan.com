# 📊 Models Layer

The Models layer acts as the data gatekeeper of the application. It encapsulates all SQL logic and database interactions, ensuring that Controllers do not need to know about the underlying schema. 

## 💡 Shared Architecture & Patterns

### Dual-User Database Strategy
To enhance security, most models support a `$useAdmin` flag in their constructor.
- **`false` (Reader):** Uses a restricted database user allowed only to perform `SELECT` queries on specific tables. Used for the public website.
- **`true` (Writer):** Uses a privileged user capable of `INSERT`, `UPDATE`, and `DELETE`. Used exclusively for the admin panel.

---

## 📂 Model Classes

### 🎵 `SongModel.php`
- **Purpose**: Handles read-only retrieval of music data for the public-facing site.
- **Attributes**:
    - `protected $db`: The PDO connection instance.
- **Key Methods**:
    - `getOfficialReleases()`: Fetches tracks linked to the 'official' song type, ordered by date. It runs a sub-query for each release to attach its active platform links.
    - `getAllMusic()`: The primary query for the Music catalog. It uses `GROUP_CONCAT` to efficiently fetch all genres associated with a song in a single row, preventing the "N+1" query problem.
    - `getSongById($id)`: Retrieves a specific song with its type, then performs separate queries to fetch its associated genres and platforms.
    - `getOtherSongsDetailed($excludeId, $limit)`: Fetches a limited set of tracks excluding the current one, used for the "More Music" suggestions on detail pages.

### 🎹 `AdminSongModel.php` (Extends `SongModel`)
- **Purpose**: Provides write access and management logic for the discography.
- **Logic**: It inherits the read methods from `SongModel` but forces the `admin` database connection.
- **Key Methods**:
    - `create()` / `update()`: Handles basic song metadata. `update` includes logic to skip the `cover_image_url` field if no new file/URL is provided.
    - `syncGenres($songId, array $genreIds)`: Manages the many-to-many relationship. It first wipes existing associations for that song and then bulk-inserts the new selection.
    - `syncPlatforms($songId, array $platformUrls)`: Similar to genres, but handles the metadata of the relationship (the specific `track_url` for that platform).
    - `getPlatformsForSong($songId)`: Returns platform names and the specific links for the admin edit form.

### 🔑 `AdminModel.php`
- **Purpose**: Manages admin credentials and Two-Factor Authentication (2FA) security.
- **Logic**: This model implements data-at-rest encryption.
- **Attributes**:
    - `private $encryptionKey`: Retrieved from `$_ENV['APP_2FA_KEY']`.
- **Key Methods**:
    - `getAdminByUsername($username)`: Fetches the singleton admin record. It includes a "Migration Fallback" logic: if it finds a plaintext username, it automatically encrypts it to modernize the DB format.
    - `encryptSecret($secret)` / `decryptSecret($data)`: Uses `AES-256-CBC` with a random 16-byte IV. The IV is prepended to the ciphertext before base64 encoding, ensuring each encryption result is unique even for the same input.
    - `enable2fa($secret)`: Encrypts the provided TOTP secret before saving it.
    - `updateCredentials()`: Dynamically builds an update query to change the encrypted username, the hashed password (using `PASSWORD_DEFAULT`), or both.

### 📋 `GenreModel.php`
- **Purpose**: Manages music genres (Electronic, Hardstyle, etc.).
- **Key Methods**:
    - `getAllWithUsage()`: Uses a `LEFT JOIN` and `COUNT` to show how many tracks are currently assigned to each genre in the admin list.
    - `isUsed($id)`: Checks the `song_genres` table. This logic is used by the Controller to decide whether to `DELETE` a record or simply `toggleStatus` (deactivate) it to preserve data integrity.
    - `toggleStatus($id)`: Flips the `is_active` boolean using the SQL `NOT` operator.

### 📻 `PlatformModel.php`
- **Purpose**: Manages streaming platforms (Spotify, YouTube) and their global icons.
- **Key Methods**:
    - `getAllActive()`: Returns platforms sorted by `display_order`, ensuring consistent branding order across the site.
    - `create()` / `update()`: Saves raw SVG strings (which are sanitized by the Controller via `Utils\SvgSanitizer` before reaching this model).
    - `isUsed($id)`: Prevents deletion of a platform if tracks are currently linked to it.

### 🏷️ `SongTypeModel.php`
- **Purpose**: Categorizes releases (Official Release, Remix, Mix).
- **Logic**: Provides standard CRUD. The `slug` is used by the frontend for CSS class assignment and filtering logic.
- **Methods**: Similar to `GenreModel`, including usage counting and safety checks before deletion.

### 📢 `AnnouncementModel.php`
- **Purpose**: Manages the site-wide notification banner.
- **Logic**: Implements a "Singleton Record" pattern. It always operates on `id = 1`.
- **Key Methods**:
    - `save($title, $bgColor, $text, $isActive)`: Checks if `id = 1` exists. If so, it updates; otherwise, it creates. This ensures there is never more than one announcement record.
    - `getActive()`: Specifically used by `PageController` to fetch only the data needed for the public banner if `is_active` is true.

### 📱 `SocialMediaModel.php`
- **Purpose**: Manages the social links appearing in the site header and footer.
- **Methods**:
    - `getActivePlatforms()`: A helper for `BaseController` to populate global site data.
    - `create()` / `update()`: Handles the storage of sanitized SVG icons for external social profiles.

---

## 🛠 Common Logic & Returns

| Method Pattern | Return Type | Logic Description |
| :--- | :--- | :--- |
| `getAll()` | `array` | Returns all records, usually sorted by name or order. |
| `getById($id)`| `array\|false` | Fetches a single row. Returns `false` if the ID doesn't exist. |
| `create(...)` | `bool\|int` | Executes an `INSERT`. Returns success boolean or the new ID. |
| `toggleStatus()`| `bool` | Executes an `UPDATE` flipping a bit/boolean column. |
| `isUsed($id)` | `bool` | Runs a `COUNT` on child relationship tables to verify constraints. |