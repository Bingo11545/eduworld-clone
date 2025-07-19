// --- Mobile Menu Toggle ---
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mainNavigation = document.getElementById('main-navigation');

    if (mobileMenuButton && mainNavigation) {
        mobileMenuButton.addEventListener('click', () => {
            mainNavigation.classList.toggle('hidden');
        });

        // Close mobile menu when a navigation link is clicked
        mainNavigation.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (!mainNavigation.classList.contains('hidden') && window.innerWidth < 768) {
                    mainNavigation.classList.add('hidden');
                }
            });
        });
    }
});

// --- Custom Message Box (instead of alert() and confirm()) ---
/**
 * Displays a custom message box.
 * @param {string} message - The message to display.
 * @param {function} [onConfirm] - Callback function for a 'Confirm' action. If provided, a 'Cancel' button also appears.
 * @param {string} [confirmText='OK'] - Text for the confirm button.
 * @param {string} [cancelText='Cancel'] - Text for the cancel button.
 */
function showMessageBox(message, onConfirm = null, confirmText = 'OK', cancelText = 'Cancel') {
    // Remove any existing message boxes to prevent stacking
    const existingMessageBox = document.querySelector('.custom-message-box-overlay');
    if (existingMessageBox) {
        existingMessageBox.remove();
    }

    const messageBoxOverlay = document.createElement('div');
    messageBoxOverlay.className = 'fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-[9999] custom-message-box-overlay';
    messageBoxOverlay.innerHTML = `
        <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm text-center relative">
            <p class="text-gray-800 text-lg font-semibold mb-4">${message}</p>
            <div class="flex justify-center space-x-4">
                <button id="message-box-confirm" class="px-6 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors">${confirmText}</button>
                ${onConfirm ? `<button id="message-box-cancel" class="px-6 py-2 bg-gray-300 text-gray-800 rounded-full hover:bg-gray-400 transition-colors">${cancelText}</button>` : ''}
            </div>
        </div>
    `;
    document.body.appendChild(messageBoxOverlay);

    document.getElementById('message-box-confirm').addEventListener('click', () => {
        messageBoxOverlay.remove();
        if (onConfirm) {
            onConfirm(true); // Indicate confirmation
        }
    });

    if (onConfirm) {
        document.getElementById('message-box-cancel').addEventListener('click', () => {
            messageBoxOverlay.remove();
            onConfirm(false); // Indicate cancellation
        });
    }
}
