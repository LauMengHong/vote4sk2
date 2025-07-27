<?php
require_once 'config.php';

// Add image column to candidates table if it doesn't exist
try {
    $pdo->exec("ALTER TABLE candidates ADD COLUMN image VARCHAR(255) DEFAULT NULL");
    echo "✅ Image column added to candidates table successfully!<br>";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ Image column already exists in candidates table.<br>";
    } else {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

// Create images directory
$upload_dir = 'images/candidates/';
if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "✅ Images directory created successfully!<br>";
    } else {
        echo "❌ Failed to create images directory!<br>";
    }
} else {
    echo "ℹ️ Images directory already exists.<br>";
}

echo "<br><a href='upload_images.php'>Go to Upload Images</a> | <a href='admin.php'>Back to Admin</a>";
?>