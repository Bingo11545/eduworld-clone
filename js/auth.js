// --- Authentication Functions ---

/**
 * Checks if a user is currently logged in based on a session token.
 * @returns {boolean} True if logged in, false otherwise.
 */
function isLoggedIn() {
    return localStorage.getItem('jwt_token') !== null;
}

/**
 * Updates the header's login/register buttons based on authentication status.
 * Shows "Welcome, User!" and "Logout" if logged in, otherwise "Login" and "Register".
 */
function updateAuthButtons() {
    const authButtonsContainer = document.getElementById('auth-buttons-container');
    if (!authButtonsContainer) return; // Exit if container not found

    authButtonsContainer.innerHTML = ''; // Clear existing buttons

    if (isLoggedIn()) {
        // User is logged in
        const welcomeSpan = document.createElement('span');
        welcomeSpan.className = 'text-gray-700 font-semibold mr-4 hidden md:inline';
        // In a real app, you'd fetch the username from the token or a profile API
        welcomeSpan.textContent = `Welcome, ${localStorage.getItem('username') || 'User'}!`;

        const logoutBtn = document.createElement('button');
        logoutBtn.className = 'px-6 py-2 bg-red-600 text-white font-semibold rounded-full hover:bg-red-700 transition duration-300 shadow-md';
        logoutBtn.textContent = 'Logout';
        logoutBtn.onclick = logoutUser;

        authButtonsContainer.appendChild(welcomeSpan);
        authButtonsContainer.appendChild(logoutBtn);
    } else {
        // User is not logged in
        const loginLink = document.createElement('a');
        loginLink.href = 'login.html';
        loginLink.className = 'px-6 py-2 bg-gray-100 text-blue-600 font-semibold rounded-full hover:bg-blue-50 transition duration-300 shadow-sm';
        loginLink.textContent = 'Login';

        const registerLink = document.createElement('a');
        registerLink.href = 'register.html';
        registerLink.className = 'px-6 py-2 bg-blue-600 text-white font-semibold rounded-full hover:bg-blue-700 transition duration-300 shadow-md';
        registerLink.textContent = 'Register';

        authButtonsContainer.appendChild(loginLink);
        authButtonsContainer.appendChild(registerLink);
    }
}

/**
 * Logs out the user by clearing the token and redirecting to the homepage.
 */
function logoutUser() {
    showMessageBox('Are you sure you want to log out?', (confirmed) => {
        if (confirmed) {
            localStorage.removeItem('jwt_token'); // Clear the token
            localStorage.removeItem('username'); // Clear username
            window.location.href = 'index.html'; // Redirect to homepage
        }
    }, 'Yes, Logout', 'No, Stay');
}

/**
 * Checks if the user is logged in. If not, redirects them to the login page.
 * This should be called on pages that require authentication (e.g., dashboard.html, community.html).
 */
function checkLoginAndRedirect() {
    if (!isLoggedIn()) {
        showMessageBox('You need to be logged in to access this page.', () => {
            window.location.href = 'login.html';
        });
    }
}

// --- Form Submission Handlers (for login.html and register.html) ---
document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.getElementById('registration-form');
    const loginForm = document.getElementById('login-form');

    // Handle Registration Form Submission
    if (registrationForm) {
        registrationForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('reg-username').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-password').value;

            try {
                const response = await fetch('backend/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: 'register', username, email, password })
                });

                const result = await response.json();

                if (result.success) {
                    showMessageBox('Registration successful! Please log in.', () => {
                        window.location.href = 'login.html';
                    });
                } else {
                    showMessageBox('Registration failed: ' + result.message);
                }
            } catch (error) {
                console.error('Error during registration:', error);
                showMessageBox('An error occurred during registration. Please try again.');
            }
        });
    }

    // Handle Login Form Submission
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;

            try {
                const response = await fetch('backend/api/auth.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: 'login', email, password })
                });

                const result = await response.json();

                if (result.success) {
                    localStorage.setItem('jwt_token', result.token); // Store the token
                    localStorage.setItem('username', result.username); // Store username for display
                    showMessageBox('Login successful! Redirecting to dashboard.', () => {
                        window.location.href = 'dashboard.html';
                    });
                } else {
                    showMessageBox('Login failed: ' + result.message);
                }
            } catch (error) {
                console.error('Error during login:', error);
                showMessageBox('An error occurred during login. Please try again.');
            }
        });
    }

    // Call updateAuthButtons on DOMContentLoaded to ensure elements are ready
    updateAuthButtons();
});
