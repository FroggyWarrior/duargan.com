/**
 * Global Admin Interface Script
 * 
 * Handles shared administrative functionality including sidebar interactions,
 * theme management, and reusable form utility functions.
 */

(function () {
    /**
     * Applies the selected theme to the administrative interface.
     * @param {string} theme - The theme to apply ('light' or 'dark').
     */
    function applyAdminTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Robustly find and update all theme toggles and icons on the page
        const toggles = document.querySelectorAll('#themeToggle, .theme-toggle');
        
        toggles.forEach(toggle => {
            // Try to find icon and label within the toggle context or by ID
            const icon = toggle.querySelector('.material-icons') || document.getElementById('themeIcon');
            const label = toggle.querySelector('#themeLabel') || document.getElementById('themeLabel');
            
            if (icon) {
                icon.textContent = theme === 'dark' ? 'light_mode' : 'dark_mode';
            }
            if (label) {
                label.textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            }
        });
    }

    // Immediate theme execution to prevent flickering
    applyAdminTheme(localStorage.getItem('theme') || 'dark');

    document.addEventListener('DOMContentLoaded', function () {
        // Re-apply theme state to sync UI elements (icons/labels) now that the DOM is ready
        applyAdminTheme(localStorage.getItem('theme') || 'dark');

        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('navOverlay');
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const closeBtn = document.getElementById('sidebarCloseBtn');
        const themeToggle = document.getElementById('themeToggle');

        if (hamburgerBtn && sidebar && overlay) {
            const openSidebar = () => {
                sidebar.classList.add('open');
                overlay.classList.add('visible');
                document.body.style.overflow = 'hidden';
            };

            const closeSidebar = () => {
                sidebar.classList.remove('open');
                overlay.classList.remove('visible');
                document.body.style.overflow = '';
            };

            hamburgerBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar);

            // Auto-close sidebar on mobile link click
            sidebar.querySelectorAll('.admin-nav-item').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) closeSidebar();
                });
            });
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                const current = document.documentElement.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', next);
                applyAdminTheme(next);
            });
        }

        /**
         * Intercepts submissions of forms marked for deletion confirmation.
         */
        document.addEventListener('submit', async function (e) {
            const form = e.target;
            if (form && (form.classList.contains('delete-form') || form.classList.contains('confirm-form'))) {
                e.preventDefault();
                const isDelete = form.classList.contains('delete-form');
                
                const title = form.getAttribute('data-confirm-title') || (isDelete ? 'Confirm Deletion' : 'Confirm Action');
                const message = form.getAttribute('data-confirm-message') || (isDelete ? 'Are you sure you want to delete this item? This action cannot be undone.' : 'Are you sure you want to proceed?');
                const btnText = form.getAttribute('data-confirm-btn') || (isDelete ? 'Delete' : 'Confirm');

                const confirmed = await showCustomConfirm(title, message, btnText);
                if (confirmed) {
                    form.submit();
                }
            }
        });

        /** --- Form-specific initializations --- */
        
        // Song form platform toggles
        document.querySelectorAll('.platform-form-checkbox').forEach(cb => {
            cb.addEventListener('change', () => AdminUtils.togglePlatformUrl(cb.value));
            if (cb.checked) AdminUtils.togglePlatformUrl(cb.value);
        });

        // Image preview from URL
        const coverUrlInput = document.getElementById('cover_image_url');
        if (coverUrlInput) {
            coverUrlInput.addEventListener('change', (e) => AdminUtils.updatePreviewFromUrl(e.target.value));
            if (coverUrlInput.value) AdminUtils.updatePreviewFromUrl(coverUrlInput.value);
        }

        // File upload preview
        const coverFileInput = document.getElementById('cover_image');
        if (coverFileInput) {
            coverFileInput.addEventListener('change', (e) => AdminUtils.updateFileName(e.target));
        }

        // Password matching
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        if (newPassword && confirmPassword) {
            const validate = () => {
                confirmPassword.setCustomValidity(newPassword.value !== confirmPassword.value ? "Passwords do not match" : "");
            };
            newPassword.addEventListener('input', validate);
            confirmPassword.addEventListener('input', validate);
        }

        // Announcement Live Preview
        const annTitle = document.getElementById('title');
        const annText = document.getElementById('text');
        if (annTitle && annText && document.getElementById('announcementPreview')) {
            annTitle.addEventListener('input', AdminUtils.updateAnnouncementPreview);
            annText.addEventListener('input', AdminUtils.updateAnnouncementPreview);
            AdminUtils.updateAnnouncementPreview();
        }
    });

    /**
     * Creates and displays a Material Design 3 styled confirmation modal.
     */
    function showCustomConfirm(title, message, confirmBtnText = 'Confirm') {
        return new Promise((resolve) => {
            const modalHtml = `
                <div class="modal-overlay" id="customConfirmModal">
                    <div class="modal">
                        <h2>${title}</h2>
                        <p>${message}</p>
                        <div class="modal-actions">
                            <button type="button" class="btn btn-cancel" id="modalCancel">Cancel</button>
                            <button type="button" class="btn btn-confirm" id="modalConfirm">${confirmBtnText}</button>
                        </div>
                    </div>
                </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = document.getElementById('customConfirmModal');
            document.getElementById('modalCancel').onclick = () => { modal.remove(); resolve(false); };
            document.getElementById('modalConfirm').onclick = () => { modal.remove(); resolve(true); };
        });
    }
})();

/**
 * Shared Form Utilities
 */
const AdminUtils = {
    /**
     * Generates a URL-friendly slug from a name string.
     * @param {string} name - The source text.
     * @param {string} inputId - ID of the slug input field.
     */
    generateSlug: function(name, inputId) {
        const slugInput = document.getElementById(inputId);
        if (slugInput) {
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-|-$/g, '');
            slugInput.value = slug;
        }
    },

    /**
     * Updates a live SVG preview container based on textarea content.
     * @param {string} textareaId - ID of the source textarea.
     * @param {string} previewId - ID of the preview div.
     */
    updateSvgPreview: function(textareaId, previewId) {
        const svgCode = document.getElementById(textareaId).value;
        const previewDiv = document.getElementById(previewId);
        const colorInput = document.getElementById('color');
        if (previewDiv) {
            previewDiv.innerHTML = svgCode.trim() ? svgCode : '<span class="material-icons">image</span>';
            if (colorInput) previewDiv.style.color = colorInput.value;
        }
    },

    /**
     * Toggles visibility of platform URL input fields in song form.
     */
    togglePlatformUrl: function(platformId) {
        const cb = document.getElementById('platform_' + platformId);
        const div = document.getElementById('platform_url_' + platformId);
        if (cb && div) div.style.display = cb.checked ? 'block' : 'none';
    },

    /**
     * Updates file name display and image preview for file uploads.
     */
    updateFileName: function(input) {
        const fileName = document.getElementById('file_name');
        if (input.files && input.files[0]) {
            fileName.textContent = 'Selected: ' + input.files[0].name;
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = document.getElementById('cover_preview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
            };
            reader.readAsDataURL(input.files[0]);
        } else if (fileName) {
            fileName.textContent = 'No file selected';
        }
    },

    /**
     * Updates image preview using a remote URL.
     */
    updatePreviewFromUrl: function(url) {
        const preview = document.getElementById('cover_preview');
        if (preview) {
            if (url) {
                preview.src = url;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }
    },

    /**
     * Updates MD3 color picker UI and syncs with hidden inputs.
     */
    syncColor: function(hex, previewId, displayId, circleId, hiddenId) {
        if (hiddenId) document.getElementById(hiddenId).value = hex;
        if (displayId) document.getElementById(displayId).textContent = hex;
        if (circleId) document.getElementById(circleId).style.backgroundColor = hex;
        if (previewId) {
            const preview = document.getElementById(previewId);
            if (preview.classList.contains('announcement-preview-banner')) {
                preview.style.backgroundColor = hex;
            } else {
                preview.style.color = hex;
            }
        }
    },

    /**
     * Updates the site announcement preview in real-time.
     */
    updateAnnouncementPreview: function() {
        const title = document.getElementById('title').value;
        const text = document.getElementById('text').value;
        const pTitle = document.getElementById('previewTitle');
        const pText = document.getElementById('previewText');
        if (pTitle) pTitle.textContent = title.trim() || 'Your title here';
        if (pText) pText.textContent = text.trim() || 'Your announcement text will appear here.';
    }
};