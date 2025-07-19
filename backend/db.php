<?php
// backend/db.php

require_once __DIR__ . '/config.php';

/**
 * Establishes a connection to the SQLite database.
 * If the database file does not exist, it will be created.
 * Also initializes the necessary tables if they don't exist.
 * @return PDO The PDO database connection object.
 */
function getDbConnection() {
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create tables if they don't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lesson_id TEXT NOT NULL UNIQUE, -- e.g., 'algebra', 'wwii'
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            url TEXT NOT NULL,
            thumbnail TEXT,
            grade_level TEXT, -- e.g., 'Grade 8', 'Grade 10'
            instructor TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS community_posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            username TEXT NOT NULL, -- Storing username for simplicity, could join with users table
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // Seed initial video data if tables are new or empty
        seedInitialData($pdo);

        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed: " . $e->getMessage());
    }
}

/**
 * Seeds initial data into the videos table if it's empty.
 * @param PDO $pdo The PDO database connection object.
 */
function seedInitialData(PDO $pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM videos");
    if ($stmt->fetchColumn() == 0) {
        $videos = [
            [
                'lesson_id' => 'algebra',
                'title' => 'Introduction to Algebra',
                'description' => 'Learn the basics of algebraic expressions and equations in this engaging lesson.',
                'url' => 'https://www.w3schools.com/html/mov_bbb.mp4', // Example video URL
                'thumbnail' => 'https://placehold.co/1280x720/a78bfa/ffffff?text=Math+Lesson',
                'grade_level' => 'Grade 8',
                'instructor' => 'Ms. Sarah Chen'
            ],
            [
                'lesson_id' => 'wwii',
                'title' => 'World War II Overview',
                'description' => 'A concise yet comprehensive look at the causes, events, and outcomes of WWII.',
                'url' => 'https://www.w3schools.com/html/mov_bbb.mp4', // Example video URL
                'thumbnail' => 'https://placehold.co/1280x720/fca5a5/ffffff?text=History+Lesson',
                'grade_level' => 'Grade 10',
                'instructor' => 'Mr. David Lee'
            ],
            [
                'lesson_id' => 'water-cycle',
                'title' => 'The Water Cycle Explained',
                'description' => 'Discover how water moves through our planet in this animated science lesson.',
                'url' => 'https://www.w3schools.com/html/mov_bbb.mp4', // Example video URL
                'thumbnail' => 'https://placehold.co/1280x720/86efac/ffffff?text=Science+Lesson',
                'grade_level' => 'Grade 5',
                'instructor' => 'Dr. Emily White'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO videos (lesson_id, title, description, url, thumbnail, grade_level, instructor) VALUES (:lesson_id, :title, :description, :url, :thumbnail, :grade_level, :instructor)");
        foreach ($videos as $video) {
            $stmt->execute($video);
        }
        error_log("Initial video data seeded.");
    }
}
?>
