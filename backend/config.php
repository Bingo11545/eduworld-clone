<?php
// backend/config.php

// Define the path to the SQLite database file
// This path is relative to the backend/ directory
define('DB_PATH', __DIR__ . '/data.sqlite');

// Secret key for JWT token generation and validation
// IMPORTANT: Change this to a strong, random string in production!
define('JWT_SECRET', 'your_super_secret_jwt_key_12345!@#$');

// Set default timezone to avoid PHP warnings
date_default_timezone_set('Africa/Nairobi'); // Ethiopia's timezone
?>
