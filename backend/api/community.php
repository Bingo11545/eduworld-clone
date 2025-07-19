<?php
// backend/api/community.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

// Simple authentication check for API access
// Include JWT validation function from auth.php
if (!function_exists('authenticateRequest')) {
    // This is a simplified way to include it. A better way would be to refactor authenticateRequest into a separate utility file.
    // For this example, we'll just require auth.php which defines validateJwt.
    require_once __DIR__ . '/auth.php'; // This also makes validateJwt available
    function authenticateRequest() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
            $decoded = validateJwt($token); // Using the validateJwt function from auth.php
            if ($decoded) {
                return $decoded; // Return decoded payload if token is valid
            }
        }
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
        exit();
    }
}


switch ($input['action']) {
    case 'create_post':
        $user = authenticateRequest(); // Ensure user is logged in
        $userId = $user['user_id'];
        $username = $user['username'];

        $title = $input['title'] ?? '';
        $content = $input['content'] ?? '';

        if (empty($title) || empty($content)) {
            echo json_encode(['success' => false, 'message' => 'Title and content are required.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO community_posts (user_id, username, title, content) VALUES (:user_id, :username, :title, :content)");
            $stmt->execute([
                'user_id' => $userId,
                'username' => $username,
                'title' => $title,
                'content' => $content
            ]);
            echo json_encode(['success' => true, 'message' => 'Post created successfully.']);
        } catch (PDOException $e) {
            error_log("Create post error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error creating post.']);
        }
        break;

    case 'get_posts':
        // Posts can be viewed by anyone, but posting requires login.
        // authenticateRequest(); // Optional: uncomment if you want to restrict viewing posts to logged-in users only.

        try {
            $stmt = $pdo->query("SELECT id, username, title, content, created_at FROM community_posts ORDER BY created_at DESC");
            $posts = $stmt->fetchAll();
            echo json_encode(['success' => true, 'posts' => $posts]);
        } catch (PDOException $e) {
            error_log("Get posts error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error fetching posts.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>
