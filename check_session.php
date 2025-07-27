<?php
require_once 'config.php';

echo "<h3>Session Debug:</h3>";
echo "Logged in: " . (isLoggedIn() ? "YES" : "NO") . "<br>";
echo "Is Admin: " . (isAdmin() ? "YES" : "NO") . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";
echo "Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
echo "Name: " . ($_SESSION['name'] ?? 'Not set') . "<br>";

echo "<br><a href='results.php'>Back to Results</a>";
?>