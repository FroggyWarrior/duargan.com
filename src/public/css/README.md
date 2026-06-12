# 🎨 CSS Layer (Styling & Design System)

This directory contains the stylesheets for both the public-facing website and the administrative dashboard. The project adheres to **Material Design 3 (MD3)** principles, utilizing a centralized system of design tokens.

## 🏗️ Design System & Theming

The application uses CSS Variables (Custom Properties) defined in `:root` within `styles.css`. This allows for global branding changes and seamless theme switching.

- **Theming Logic**: Light and Dark modes are toggled via the `data-theme` attribute on the `<html>` element. Styles use `color-mix()` for dynamic transparency and state variations.
- **Tokens**: Includes variables for `--primary`, `--surface`, `--outline`, and their respective "On-" variants (e.g., `--on-primary`).

## 📂 File Documentation

### 🌐 `styles.css`
The main stylesheet for the producer portfolio. It is organized into functional sections:
- **Global Layout**: sticky header, navigation "pills", logo filtering for theme compatibility, and the responsive mobile menu.
- **Homepage**: Hero section for the latest release (using background blurs), platform buttons with branding colors, and the official releases grid.
- **Music Library**: Styles for the filter chips, sort buttons, and the interactive music cards. It uses the **Stretched Link Pattern** to make cards clickable while maintaining access to platform icons.
- **Song Details**: Hero layout for high-res cover art, metadata tags, and the social sharing icon system with custom tooltips.

### 🔒 `admin.css`
Specific styling for the administrative interface, focusing on usability and data management:
- **Sidebar & Navigation**: A fixed-position vertical drawer that transitions into a mobile-friendly hamburger menu.
- **Authentication**: Minimalist and secure layouts for the login and 2FA verification cards.
- **Form System**: MD3-inspired floating label inputs (`.material-form-group`), custom file upload triggers, and interactive color pickers.
- **Management Grids**: Compact "Song Boxes" and "Genre Items" for efficient CRUD operations.

## ⚡ Key Techniques Used
- **Backdrop Filters**: Used in mobile menus for an "Expressive" glassmorphism effect.
- **Responsive Flex/Grid**: Intelligent use of `minmax()` and `repeat(auto-fill)` to handle any screen size.
- **SVG Styling**: SVGs use `fill="currentColor"` to inherit styles from parent elements, ensuring they look correct in both themes.