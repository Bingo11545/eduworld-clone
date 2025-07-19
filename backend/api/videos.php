<?php
// backend/api/videos.php

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

// Include JWT validation function from auth.php
// In a real project, this would be a shared utility or a dedicated auth class
if (!function_exists('validateJwt')) {
    // This is a simplified way to include it. A better way would be to refactor validateJwt into a separate utility file.
    // For this example, we'll just require auth.php which defines it.
    require_once __DIR__ . '/auth.php';
}


switch ($input['action']) {
    case 'get_video':
        authenticateRequest(); // Ensure user is logged in

        $lessonId = $input['lesson_id'] ?? null;

        if (empty($lessonId)) {
            echo json_encode(['success' => false, 'message' => 'Lesson ID is required.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM videos WHERE lesson_id = :lesson_id");
            $stmt->execute(['lesson_id' => $lessonId]);
            $video = $stmt->fetch();

            if ($video) {
                echo json_encode(['success' => true, 'video' => $video]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Video not found.']);
            }
        } catch (PDOException $e) {
            error_log("Get video error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error fetching video.']);
        }
        break;

    case 'get_all_videos': // Example to get all videos (e.g., for a course listing)
        authenticateRequest();

        try {
            $stmt = $pdo->query("SELECT * FROM videos ORDER BY title ASC");
            $videos = $stmt->fetchAll();
            echo json_encode(['success' => true, 'videos' => $videos]);
        } catch (PDOException $e) {
            error_log("Get all videos error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error fetching all videos.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>
