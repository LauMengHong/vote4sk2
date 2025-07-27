<?php
require_once 'includes/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_POST && isset($_POST['action']) && $_POST['action'] === 'vote') {
    $candidate_id = (int)$_POST['candidate_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check if user already voted
        $stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user['has_voted']) {
            echo json_encode(['success' => false, 'message' => 'You have already voted!']);
            exit();
        }
        
        // Check if voting is open
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'voting_open'");
        $stmt->execute();
        $voting_open = $stmt->fetchColumn() == '1';
        
        if (!$voting_open) {
            echo json_encode(['success' => false, 'message' => 'Voting is currently closed!']);
            exit();
        }
        
        // Check if candidate exists
        $stmt = $pdo->prepare("SELECT id FROM candidates WHERE id = ?");
        $stmt->execute([$candidate_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid candidate!']);
            exit();
        }
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Record the vote
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $candidate_id]);
        
        // Update candidate vote count
        $stmt = $pdo->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$candidate_id]);
        
        // Mark user as voted
        $stmt = $pdo->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode(['success' => true, 'message' => 'Vote recorded successfully!']);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request!']);
}
?>