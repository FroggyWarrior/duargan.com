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
        const icon = document.getElementById('themeIcon');
        const label = document.getElementById('themeLabel');
        if (!icon) return;
        
        if (theme === 'dark') {
            icon.textContent = 'light_mode';
            label.textContent = 'Light Mode';
        } else {
            icon.textContent = 'dark_mode';
            label.textContent = 'Dark Mode';
        }
    }

    // Immediate theme execution to prevent flickering
    applyAdminTheme(localStorage.getItem('theme') || 'dark');

    document.addEventListener('DOMContentLoaded', function () {
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
        if (previewDiv) {
            previewDiv.innerHTML = svgCode.trim() ? svgCode : '<span class="material-icons">image</span>';
        }
    }
};