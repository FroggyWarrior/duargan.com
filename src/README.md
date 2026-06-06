# 🛠️ Source Code Documentation

This directory contains the core logic and assets for the Artist Portfolio CMS. The application is built using a custom **MVC (Model-View-Controller)** architecture to ensure a clean separation of concerns, scalability, and maintainability.

## 📂 Folder Structure

```text
src/
├── app/                        # Application logic
│   ├── Controllers/            # Handles user input and interactions
│   │   └── Admin/              # Protected administrative controllers
│   ├── Core/                   # Framework engine (Router, DB, Base classes)
│   ├── Models/                 # Database logic and data structures
│   ├── Utils/                  # Helper classes (e.g., SvgSanitizer)
│   └── Views/                  # HTML templates and UI components
│       ├── admin/              # Admin dashboard view templates
│       │   ├── announcement/   # Announcement management views
│       │   ├── credentials/    # Login and 2FA settings
│       │   ├── genres/         # Genre CRUD views
│       │   ├── platforms/      # Music platform management
│       │   ├── social_media/   # Social media link management
│       │   └── song_types/     # Song category management
│       └── partials/           # Reusable components (Header, Footer)
└── public/                     # Document root (Web accessible)
    ├── css/                    # Global and component stylesheets
    ├── img/                    # Static images and logos
    │   └── covers/             # Uploaded song cover art
    └── js/                     # Client-side interactivity scripts
```

---

## 🏗️ MVC Architecture & Data Flow

The project follows a strict request-response lifecycle driven by the MVC pattern.

### 1. The Entry Point (**public**)
Every request is directed to `public/index.php` via `.htaccess`. This file acts as the **Front Controller**, initializing the application environment and the routing system.

### 2. The Engine (**Core**)
The **Core** components handle the heavy lifting before logic reaches your controllers:
- **Router:** Parses the URL and determines which Controller and method to execute.
- **Database:** Manages singleton connections to MariaDB.
- **Base Controller:** Provides common methods like `render()` and `redirect()`.

### 3. The Traffic Cop (**Controllers** & **Admin**)
Controllers receive the request from the Core.
- They validate user input (via POST/GET).
- They communicate with **Models** to fetch or save data.
- They determine which **View** should be displayed.
- **Admin Controllers** are nested within the `Admin/` namespace and extend a `BaseAdminController` for session and 2FA verification.

### 4. The Data Gatekeeper (**Models**)
Models like `GenreModel` or `PlatformModel` are responsible for the data layer. They contain the SQL queries and interact with the database using PDO. They ensure that the rest of the application doesn't need to know about the database schema.

### 5. The Presentation Layer (**Views**)
Views are simple PHP files that generate HTML.
- **Partial Views:** Files in `partials/` (like headers and footers) are reused across different pages to maintain consistency.
- **Admin Views:** Specialized templates for managing **genres**, **platforms**, **social_media**, and **announcements**.

### 6. Supporting Utilities (**Utils**)
Classes in the `Utils/` folder provide cross-cutting functionality, such as `SvgSanitizer`, which ensures that raw SVG icons uploaded for platforms or social media are safe from XSS attacks.

---

## 🔄 Typical Data Flow Example

1.  **Request:** A user visits `/admin/genres`.
2.  **Routing:** `Core` maps the URL to `Admin\GenresController::index()`.
3.  **Logic:** The `GenresController` calls `GenreModel::getAllWithUsage()`.
4.  **Data:** The **Model** queries the database and returns an array of genres.
5.  **Rendering:** The **Controller** passes this data to the `admin/genres/index` **View**.
6.  **Response:** The View merges the data with the **partials** (header/footer) and the browser receives the final HTML, loading styles from **css** and scripts from **js**.

---

## 🖼️ Media Handling

When a new song is stored via the `DashboardController`:
1. The image is processed and resized.
2. The final **WebP** file is stored in `public/img/covers/`.
3. The relative path is saved in the database via the **Model**.

---

## 🔐 Security Components

- **credentials:** Managed within the admin views for handling authentication.
- **2FA:** Integrated into the admin flow to protect the dashboard.
- **CSRF:** Tokens are generated in the `BaseController` and verified on all POST requests.
