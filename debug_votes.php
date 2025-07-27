<?php
require_once 'config.php';

echo "<h3>Vote Debug:</h3>";

// Count votes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes");
$stmt->execute();
$count = $stmt->fetchColumn();
echo "Total votes in database: " . $count . "<br><br>";

// Show all votes
$stmt = $pdo->prepare("SELECT v.*, u.name as user_name, c.name as candidate_name FROM votes v JOIN users u ON v.user_id = u.id JOIN candidates c ON v.candidate_id = c.id");
$stmt->execute();
$votes = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>Vote ID</th><th>User</th><th>Candidate</th><th>Time</th></tr>";
foreach($votes as $vote) {
    echo "<tr>";
    echo "<td>" . $vote['id'] . "</td>";
    echo "<td>" . $vote['user_name'] . "</td>";
    echo "<td>" . $vote['candidate_name'] . "</td>";
    echo "<td>" . $vote['voted_at'] . "</td>";
    echo "</tr>";
}
echo "</table>";

if (count($votes) > 1) {
    echo "<br><form method='POST'>";
    echo "<button type='submit' name='clean_duplicates' style='background:red;color:white;padding:10px;'>Clean Duplicate Votes</button>";
    echo "</form>";
}

if ($_POST && isset($_POST['clean_duplicates'])) {
    // Keep only the latest vote per user
    $pdo->exec("DELETE v1 FROM votes v1 INNER JOIN votes v2 WHERE v1.id < v2.id AND v1.user_id = v2.user_id");
    echo "<br>✅ Duplicates cleaned! <a href='results.php'>Check results</a>";
}
?>