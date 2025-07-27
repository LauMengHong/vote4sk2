<?php
require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) { 
    header('Location: login.php'); 
    exit(); 
}

// Get candidates with votes
$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC");
$stmt->execute();
$candidates = $stmt->fetchAll();

// Get total votes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes");
$stmt->execute();
$total_votes = $stmt->fetchColumn();

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="voting_results_' . date('Y-m-d') . '.csv"');

// Create CSV content
$output = fopen('php://output', 'w');

// CSV headers
fputcsv($output, ['Rank', 'Contestant Name', 'Votes', 'Percentage']);

// CSV data
foreach ($candidates as $index => $candidate) {
    $percentage = $total_votes > 0 ? round(($candidate['votes'] / $total_votes) * 100, 1) : 0;
    fputcsv($output, [
        $index + 1,
        $candidate['name'],
        $candidate['votes'],
        $percentage . '%'
    ]);
}

fclose($output);
exit();
?>