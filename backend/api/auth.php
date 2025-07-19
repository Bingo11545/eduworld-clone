<?php
// backend/api/auth.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Simple JWT implementation (for demonstration, not for production without proper library)
// In a real app, use a robust JWT library like firebase/php-jwt
function generateJwt($userId, $username, $email) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode([
        'user_id' => $userId,
        'username' => $username,
        'email' => $email,
        'exp' => time() + (60 * 60) // Token valid for 1 hour
    ]);

    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function validateJwt($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return false;
    }

    list($header, $payload, $signature) = $parts;

    $expectedSignature = hash_hmac('sha256', $header . "." . $payload, JWT_SECRET, true);
    $expectedBase64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expectedSignature));

    if ($expectedBase64UrlSignature !== $signature) {
        return false; // Invalid signature
    }

    $decodedPayload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);

    if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
        return false; // Token expired
    }

    return $decodedPayload;
}


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow all origins for local development
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'No action specified.']);
    exit();
}

$pdo = getDbConnection();

switch ($input['action']) {
    case 'register':
        $username = $input['username'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit();
        }

        // Basic email validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
            exit();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword]);
            echo json_encode(['success' => true, 'message' => 'Registration successful.']);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // SQLite unique constraint failed error code
                echo json_encode(['success' => false, 'message' => 'Email or username already exists.']);
            } else {
                error_log("Registration error: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error during registration.']);
            }
        }
        break;

    case 'login':
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $token = generateJwt($user['id'], $user['username'], $user['email']);
                echo json_encode(['success' => true, 'message' => 'Login successful.', 'token' => $token, 'username' => $user['username']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error during login.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>
