# ⚡ JavaScript Layer (Interactivity & Logic)

This directory contains the client-side logic that enhances the user experience and powers the administrative tools.

## 📂 File Documentation

### 🌐 `script.js` (Public Site Logic)
Handles global interactions for visitors:
- **Theme Management**: Persists the user's preference in `localStorage` and updates the UI icons.
- **Mobile Navigation**: Manages the state of the burger menu, including body scroll locking and backdrop interaction.
- **Scroll Animations**: Uses the **Intersection Observer API** for high-performance "fade-in" effects as elements enter the viewport.
- **Music Library Engine**: 
    - **Filtering**: Real-time filtering by genre and release type using `data-*` attributes.
    - **Sorting**: Client-side date-based sorting (Newest/Oldest).
- **Social Tools**: Implements the `navigator.clipboard` API for the "Copy Link" feature with a visual tooltip fallback.

### 🔒 `admin.js` (CMS Interface Logic)
A robust set of utilities for the admin panel, encapsulated in an IIFE to prevent global namespace pollution:
- **Confirmation System**: An `async` custom modal system (`showCustomConfirm`) that replaces standard browser alerts for a native MD3 look.
- **Form Utilities (`AdminUtils`)**:
    - `generateSlug()`: Real-time generation of URL-friendly strings from names.
    - `updateSvgPreview()`: Live rendering of raw SVG code pasted into textareas.
    - `syncColor()`: Updates the UI and hidden inputs when brand colors are changed.
    - `handleCoverUpload()`: Handles the logic for showing local file previews versus remote URL previews.
- **2FA Initialization**: Bootstraps the `qrcode.min.js` library to render TOTP setup codes.
- **Event Delegation**: Centralized click listeners for alerts, file triggers, and custom checkboxes to keep the DOM clean.

### 📦 `qrcode.min.js`
A lightweight external library used exclusively within the admin credentials section to generate setup QR codes for authenticator apps.

## 🔄 Interaction Strategy
- **Performance**: Heavy use of event delegation and the Intersection Observer API to minimize the number of active listeners.
- **Security**: JS logic respects the Content Security Policy (CSP) by avoiding inline handlers where possible, favoring centralized listeners in `admin.js`.