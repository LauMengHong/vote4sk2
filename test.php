<?php
require_once 'config.php';

// Test login directly
$email = 'admin@4sk2.com';
$password = 'admin123';

echo "<h3>Testing Login...</h3>";

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found: " . $user['name'] . " (" . $user['role'] . ")<br>";
        echo "Email: " . $user['email'] . "<br>";
        
        // Test password
        if (password_verify($password, $user['password'])) {
            echo "✅ Password correct!<br>";
            echo "<strong>Login should work now</strong><br>";
        } else {
            echo "❌ Password incorrect. Fixing...<br>";
            
            // Update password
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$newHash, $email]);
            
            echo "✅ Password updated! Try login now.<br>";
        }
    } else {
        echo "❌ User not found. Creating...<br>";
        
        // Create admin user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Admin', $email, $hashedPassword, 'admin']);
        
        echo "✅ Admin user created!<br>";
    }
    
    echo "<br><a href='login.php'>Try Login Now</a>";
    
} catch(PDOException $e) {
    echo "Database error: " . $e->getMessage();
    echo "<br>Run debug.php first to create database.";
}
?>