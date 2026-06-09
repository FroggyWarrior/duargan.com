# 🎵 Artist Portfolio CMS
### The source code for Duargan's Official Website

This repository contains the professional, lightweight, and high-performance source code used for the official website of music producer **Duargan**. 

While this instance serves as Duargan's portfolio, the project is built as a reusable and SEO-optimized CMS engine designed specifically for electronic artists and producers to manage their discography, announcements, and social presence.

![License](https://img.shields.io/badge/license-GPL--3.0-blue.svg)
![Tech](https://img.shields.io/badge/tech-PHP%20|%20MariaDB%20|%20Javascript%20|%20CSS%20|%20Docker-orange.svg)

---

## ✨ Features

### 🌐 Public Site
*   **Responsive MD3 Design:** A modern and responsive interface following Material Design 3 principles with native Dark/Light mode support.
*   **Dynamic Discography:** Client-side filtering by genre and release type, with date-based sorting.
*   **SEO & Social Sharing:** Dynamic Open Graph (OG) tags for every song. When you share a track, social media platforms show the specific cover art and title.
*   **High Performance & Lightweight:** Native lazy-loading for images and Intersection Observer animations for smooth scroll reveals. Lightweight code (src folder without images is less than 1MB).
*   **Custom Announcements:** A customizable banner for important news (new releases, tours, etc.).

### 🔒 Admin Panel
*   **Full CRUD Management:** Easily add, edit, or delete songs, genres, platforms, and social media.
*   **Smart Image Processing:** Automatically resizes high-res uploads to 900px and converts them to **WebP** for lightning-fast loading without quality loss.
*   **Secure Authentication:** Protected by Two-Factor Authentication (2FA/TOTP) and CSRF protection.
*   **SVG Integration:** Manage platform icons using raw SVG code with built-in XSS sanitization.
*   **Storage Cleanup:** Automatically deletes old image files from the server when a song is updated or removed.

---

## 🚀 Getting Started

### 🐳 Development with Docker

This project is fully containerized for a "zero-config" development experience.

#### How the containers work:
*   **`docker-compose.yml`**: Orchestrates three interconnected services:
    *   **`web`**: The main application server. It builds from the local `Dockerfile`, maps the web port, and mounts the `./src` folder as a volume for real-time code updates. It also injects a custom `php.ini` to override default upload limits.
    *   **`db`**: A MariaDB container that persists data in `./data/mysql`. It automatically executes `mysql/init.sql` on the first run to bootstrap the database schema.
    *   **`phpmyadmin`**: A GUI for database management accessible at port 8080.
*   **`Dockerfile`**: (Located in the root) This file defines the server environment. It uses an official PHP-Apache image, installs the `GD` library (required for the CMS's smart image resizing), enables `pdo_mysql` for database communication, and activates Apache's `mod_rewrite` to handle the MVC routing.

#### Prerequisites
*   [Docker] installed.

#### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/FroggyWarrior/duargan.com.git
    cd duargan.com
    ```

2.  **Configure environment variables:**
    Create a `.env` file in the root directory (copy from `.env.example` if provided) and set your database credentials.

3.  **Spin up the containers:**
    ```bash
    docker compose up -d
    ```

4.  **Access the site locally:**
    *   Public Site: `http://localhost`
    *   Admin Panel: `http://localhost/admin` (Credentials: `admin`, `admin` if you use `example.sql`)
    *   Database (phpMyAdmin): `http://localhost:8080` (Credentials - `root` - `root_password`) - Here you can import `example.sql` and play with it.

### 🌐 Deployment (Non-Docker)

If you prefer to deploy on a traditional shared hosting service (CPanel) or a standard VPS:

1.  **Requirements:** 
    *   PHP 8.1+ with `gd`, `pdo_mysql`, and `mbstring` extensions.
    *   MySQL or MariaDB server.
    *   Apache with `mod_rewrite` enabled.
2.  **Upload:** Move the contents of the `src/` directory to your server's root.
3.  **Document Root:** Configure your web server to point its Document Root to the `/public` directory. **Never point it to the project root**, as this would expose your source code and `.env` files.
    *   If you're using a shared hosting service, you probably won't be able to change what the Document Root points to. In that case, move the contents of `public` into public_html (or whatever it's called in your website), and the rest (the `app` folder and your `.env` file) into another folder outside of `public_html`. After that, change the `$base_dir` variable on `index.php` to point to that folder.
4.  **Database:** Create a database and import yours. Change the values in `Database.php` with your own.
5.  **Environment:** Create a `.env` file in the root (copy from `.env.example`) and fill in your own credentials.
6.  **Permissions:** Ensure the `public/img/covers/` directory is writable by the web server user (`www-data`).

---

## 🛠 Project Structure

The project is powered by a custom-built PHP MVC (Model-View-Controller) architecture:

*   `app/Core/`: The engine (Router, Database Singleton, Base Controller).
*   `app/Controllers/`: Application logic (Public pages vs. Admin dashboard).
*   `app/Models/`: Database interactions and data logic.
*   `app/Views/`: HTML templates and page-specific scripts.
*   `app/Utils/`: Security utilities (2FA, SVG Sanitizer).
*   `public/`: The only directory accessible to the web (Router index, CSS, JS, Images).

---

## 🎨 Artist's Customization Guide

If you are an artist forking this repository to build your own official website, here is how to customize it:

### 1. Branding & Identity
*   **Logo:** Replace `public/img/logo.svg` with your own vector logo.
*   **Profile Picture:** Replace `public/img/me.jpg` with your artist photo.
*   **Colors:** Open `public/css/styles.css` and modify the CSS variables in `:root`. Changing `--primary` will update the accent color across the entire site.

### 2. Personal Info
*   **About Me:** Edit `app/Views/about.php` to update your biography.
*   **Contact:** Update the email addresses and Discord links in `app/Views/contact.php`.
*   **SEO:** Set your default site title and keywords in `app/Controllers/BaseController.php`.

### 3. Server Limits
If you plan to upload very large high-res covers, ensure your hosting provider or `php.ini` allows files up to 20MB. This is already configured in the provided `src/php.ini` and `src/public/.htaccess`.

---

## 🔒 Security
*   **CSRF Protection:** All administrative POST requests require a valid token.
*   **2FA:** It is highly recommended to enable 2FA in the "Credentials" section of the admin panel immediately after setup.
*   **Dual-User DB:** The app uses separate database users for reading (public) and writing (admin) to minimize the impact of potential SQL injections.
*   **XSS Prevention:** All user-generated SVG content is sanitized before being rendered.

---

## 📅 Roadmap / To-Do List

The project is actively evolving. Here are the planned features:
## 🔵 High priority
- [ ] **Albums Support:** Ability to group tracks into EPs and Albums.
- [ ] **Improved Workflow:** Add new song types and genres directly from the song creation form without leaving the page.
- [ ] **Play and download songs** Add a player to song.php so visitors can play songs, and download them if it's copyright free.
- [ ] **License of the track:** Shows license info, i.e. Copyright free, Copyright legal text, Creative Commons, etc.
- [ ] **Release info:** For official releases, show the UPC code and other relevant info.
## 🟢 Medium priority
- [ ] **Smart Onboarding:** Automatic setup wizard for the admin panel if no database is detected, making the CMS truly plug-and-play, with import and export feature.
- [ ] **Dynamic Content:** Make 100% of the public site content (bio, contact info) editable via the admin dashboard.
- [ ] **Customizable SEO** Edit SEO info directly from the Admin Panel.
## 🟡 Low priority
- [ ] **UI Overhaul:** Migration to **Material 3 Expressive** with enhanced transparency effects and smooth motion transitions.
- [ ] **Multi-selection editing** Change the genre, release date or song type of multiple songs at once.
- [ ] **Lyrics and music videos** Embedded YouTube player and lyrics section in the songs details page.
- [ ] **Q&A Section** Add a Frequent Q&A section in the about page.
- [ ] **Donations** Add a donation button to redirect to platflorms like ko-fi or Patreon.
## 🔴 Optional (TBD, might discard in the future)
- [ ] **Concerts and merch** Integrate with third-party platforms (or maybe build an open source alternative?) for selling tickets and merch.
- [ ] **Stats and metrics** Show anonimized stats directly in the admin panel for easy understanding of the fanbase.
- [ ] **Likes and comments** Allow listeners to rate songs and leave feedback, improving the social interaction with the artist.
- [ ] **Contact form** Basic contact form in the contact page.
- [ ] **Random song** Show a random song with a button, for listeners to discover new music.
- [ ] **Blog** Update fans with what you're working on, what's coming next, or how something went.

💡 **Have a suggestion?** We want to hear from you! Please feel free to open an issue to ask for new features or report bugs.

---

## 📜 License

This project is licensed under the **GPL-3.0 License**. You are free to fork, modify, and distribute this software, provided that the source code remains open.

---

## 🤝 Contributing

Contributions are welcome! If you find a bug or have a feature request, please open an issue or submit a pull request.

---

Made with ❤️ by FroggyWarrior