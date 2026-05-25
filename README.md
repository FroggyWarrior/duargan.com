# 🎵 Artist Portfolio CMS
### The source code for Duargan's Official Website

This repository contains the professional, high-performance source code used for the official website of music producer **Duargan**. 

While this instance serves as Duargan's portfolio, the project is built as a reusable and SEO-optimized CMS engine designed specifically for electronic artists and producers to manage their discography, announcements, and social presence.

![Version](https://img.shields.io/badge/version-1.0.0-purple.svg)
![License](https://img.shields.io/badge/license-GPL--3.0-blue.svg)
![Tech](https://img.shields.io/badge/tech-PHP%20|%20MariaDB%20|%20Docker-orange.svg)

---

## ✨ Features

### 🌐 Public Site
*   **Responsive MD3 Design:** A modern, mobile-first interface following Material Design 3 principles with native Dark/Light mode support.
*   **Dynamic Discography:** Client-side filtering by genre and release type, with date-based sorting.
*   **SEO & Social Sharing:** Dynamic Open Graph (OG) tags for every song. When you share a track, social media platforms show the specific cover art and title.
*   **High Performance:** Native lazy-loading for images and Intersection Observer animations for smooth scroll reveals.
*   **Site-wide Announcements:** A customizable banner for important news (new releases, tours, etc.).

### 🔒 Admin Panel
*   **Full CRUD Management:** Easily add, edit, or delete songs, genres, platforms, and social links.
*   **Smart Image Processing:** Automatically resizes high-res uploads to 900px and converts them to **WebP** for lightning-fast loading without quality loss.
*   **Secure Authentication:** Protected by Two-Factor Authentication (2FA/TOTP) and CSRF protection.
*   **SVG Integration:** Manage platform icons using raw SVG code with built-in XSS sanitization.
*   **Storage Cleanup:** Automatically deletes old image files from the server when a song is updated or removed.

---

## 🚀 Quick Start (Development Setup)

This project is fully containerized with Docker for an instant development environment.

### Prerequisites
*   [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed.

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/YourUsername/your-repo-name.git
    cd your-repo-name
    ```

2.  **Configure environment variables:**
    Create a `.env` file in the root directory (copy from `.env.example` if provided) and set your database credentials.

3.  **Spin up the containers:**
    ```bash
    docker compose up -d
    ```

4.  **Access the site:**
    *   Public Site: `http://localhost`
    *   Admin Panel: `http://localhost/admin`
    *   Database (phpMyAdmin): `http://localhost:8080`

---

## 🛠 Project Structure

The project is powered by a custom-built PHP MVC (Model-View-Controller) architecture:

*   `app/Core/`: The engine (Router, Database Singleton, Base Controller).
*   `app/Controllers/`: Application logic (Public pages vs. Admin dashboard).
*   `app/Models/`: Database interactions and data logic.
*   `app/Views/`: HTML templates and page-specific scripts.
*   `app/Utils/`: Security utilities (2FA, SVG Sanitizer, Image Processing).
*   `public/`: The only directory accessible to the web (Entry point, CSS, JS, Images).

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

## 🛡 Security
*   **CSRF Protection:** All administrative POST requests require a valid token.
*   **2FA:** It is highly recommended to enable 2FA in the "Credentials" section of the admin panel immediately after setup.
*   **Dual-User DB:** The app uses separate database users for reading (public) and writing (admin) to minimize the impact of potential SQL injections.
*   **XSS Prevention:** All user-generated SVG content is sanitized before being rendered.

---

## 📜 License

This project is licensed under the **GPL-3.0 License**. You are free to fork, modify, and distribute this software, provided that the source code remains open.

---

## 🤝 Contributing

Contributions are welcome! If you find a bug or have a feature request, please open an issue or submit a pull request.

*Developed for the official Duargan website.*
```
