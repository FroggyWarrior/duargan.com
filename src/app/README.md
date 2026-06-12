# 🚀 Application Logic Layer (`app`)

This directory serves as the core of the **Artist Portfolio CMS**. It houses all the server-side logic, data management, and template rendering systems, organized strictly following the **Model-View-Controller (MVC)** architectural pattern.

## 📂 Folder Breakdown

### 🎮 Controllers/ (The Brain)
Controllers act as the glue between the user's request and the application's response.
- **Logic:** They capture input from `$_GET` or `$_POST`, invoke the appropriate **Models** to retrieve or store data, and finally determine which **View** to render.
- **Sub-structure:** Includes a specialized `Admin/` directory for dashboard logic, which is protected by session verification and CSRF checks.
- *Detailed docs available in the Controllers subdirectory.*

### ⚙️ Core/ (The Engine)
The foundational classes that define how the custom framework operates.
- **Logic:** This folder contains the **Router**, which maps URLs to controller methods; the **Database** class, which implements the Singleton pattern for efficient connections; and the base **Controller** class.
- **Security:** This layer enforces the "Dual-User" database architecture (Reader for public content, Writer for admin operations).

### 📊 Models/ (The Memory)
The data access layer that encapsulates all SQL interactions.
- **Logic:** Each model represents a specific entity (Songs, Genres, Platforms, etc.). They handle complex queries (using `JOIN`s and `GROUP_CONCAT`) and ensure that database schema details remain hidden from the controllers.
- **Encryption:** Secure models like `AdminModel` handle data-at-rest encryption for sensitive fields using AES-256-CBC.

### 🛠️ Utils/ (The Toolkit)
Self-contained utility classes providing specialized security functionality.
- **Logic:** Focuses on hardened security tasks. It includes the `SvgSanitizer` for XSS-free icon uploads and `TOTPAuthenticator` for industry-standard Two-Factor Authentication (2FA).

### 🖼️ Views/ (The Face)
The presentation layer consisting of plain PHP templates.
- **Logic:** These files generate the final HTML sent to the browser. They use **Partials** (reusable snippets like headers and footers) to ensure design consistency.
- **Design:** Implements Material Design 3 (MD3) principles and handles dynamic theme switching.

## 🔄 High-Level Interaction Flow
1. **Request:** A user interaction triggers a request at the public entry point.
2. **Routing:** `Core\Router` parses the URI and instantiates the correct `Controller`.
3. **Processing:** The `Controller` communicates with a `Model` to interact with the database.
4. **Rendering:** The `Controller` extracts data into a `View`.
5. **Response:** The merged HTML is sent back to the browser.

---
*For detailed technical specifications on each component, please refer to the README files within each subfolder.*