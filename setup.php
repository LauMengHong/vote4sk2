<?php
// Database setup script
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS class_voting");
    $pdo->exec("USE class_voting");
    
    // Create tables and insert data
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);
    
    echo "<h2>✅ Database setup completed successfully!</h2>";
    echo "<p>Admin login: admin@4sk2.com / admin123</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    
} catch(PDOException $e) {
    echo "<h2>❌ Setup failed: " . $e->getMessage() . "</h2>";
}
?>