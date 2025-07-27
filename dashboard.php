<?php
require_once 'config.php';
if (!isLoggedIn()) { header('Location: login.php'); exit(); }

$stmt = $pdo->prepare("SELECT has_voted FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY name");
$stmt->execute();
$candidates = $stmt->fetchAll();

if ($_POST && isset($_POST['vote'])) {
    if (!$user['has_voted']) {
        $candidate_id = (int)$_POST['candidate_id'];
        
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO votes (user_id, candidate_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $candidate_id]);
        
        $stmt = $pdo->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
        $stmt->execute([$candidate_id]);
        
        $stmt = $pdo->prepare("UPDATE users SET has_voted = 1 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $pdo->commit();
        
        $success = "Vote recorded successfully!";
        $user['has_voted'] = 1;
    }
}

if ($_POST && isset($_POST['delete_vote']) && isset($_POST['new_password'])) {
    if ($user['has_voted']) {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("SELECT candidate_id FROM votes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $vote = $stmt->fetch();
        
        if ($vote) {
            $stmt = $pdo->prepare("DELETE FROM votes WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $stmt = $pdo->prepare("UPDATE candidates SET votes = votes - 1 WHERE id = ?");
            $stmt->execute([$vote['candidate_id']]);
            
            $stmt = $pdo->prepare("UPDATE users SET has_voted = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $pdo->commit();
            
            $success = "Vote reset successfully!";
            $user['has_voted'] = 0;
        }
    }
}

// Get user's vote if they voted
$user_vote = null;
if ($user['has_voted']) {
    $stmt = $pdo->prepare("SELECT c.name FROM votes v JOIN candidates c ON v.candidate_id = c.id WHERE v.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_vote = $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Dashboard - Class 4SK2</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .dashboard-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.6rem 1.2rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-links a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }
        .welcome-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            color: white;
        }
        .welcome-title {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: white;
            display: block;
            margin-bottom: 0.3rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .voting-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .candidates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        .candidate-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: rgba(102, 126, 234, 0.3);
        }
        .candidate-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .candidate-name {
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .candidate-votes {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .vote-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            width: 100%;
        }
        .vote-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .vote-btn:disabled {
            background: #10b981;
            cursor: not-allowed;
            transform: none;
        }
        .reset-section {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        .reset-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: 1px solid rgba(16, 185, 129, 0.5);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        .user-vote-info {
            background: linear-gradient(135deg, #10b981, #059669);
            border: 1px solid rgba(16, 185, 129, 0.5);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        .quick-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }
        .action-btn {
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <nav class="nav">
                <a href="dashboard.php" class="logo">
                    <i class="fas fa-vote-yea"></i> Voting Dashboard
                </a>
                <div class="nav-links">
                    <span style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="results.php"><i class="fas fa-chart-bar"></i> Results</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </header>

        <div class="welcome-section">
            <h1 class="welcome-title">🎭 Class 4SK2 Impersonator Contest</h1>
            <p style="opacity: 0.9; font-size: 1.1rem;">Cast your vote for the most talented impersonator!</p>
            
            <div class="quick-actions">
                <a href="results.php" class="action-btn btn-primary">
                    <i class="fas fa-chart-line"></i> View Live Results
                </a>
                <a href="profile.php" class="action-btn btn-secondary">
                    <i class="fas fa-user-cog"></i> Manage Profile
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($candidates); ?></span>
                <span class="stat-label">Total Contestants</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $user['has_voted'] ? '✅' : '❌'; ?></span>
                <span class="stat-label"><?php echo $user['has_voted'] ? 'Vote Cast' : 'Not Voted'; ?></span>
            </div>
            <?php if ($user['has_voted'] && $user_vote): ?>
            <div class="stat-card">
                <span class="stat-number">🎯</span>
                <span class="stat-label">Voted for: <?php echo htmlspecialchars($user_vote); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($user['has_voted']): ?>
            <div class="user-vote-info">
                <h4><i class="fas fa-check-circle"></i> Thank you for voting!</h4>
                <p>You have successfully cast your vote for <strong><?php echo htmlspecialchars($user_vote); ?></strong></p>
            </div>
        <?php endif; ?>

        <div class="voting-card">
            <h2 style="margin-bottom: 1.5rem; color: #374151; text-align: center;">
                <i class="fas fa-users"></i> Choose Your Favorite Contestant
            </h2>
            
            <div class="candidates-grid">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="candidate-card">
                        <?php if (!empty($candidate['image']) && file_exists('images/candidates/' . $candidate['image'])): ?>
                            <img src="images/candidates/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                 class="candidate-avatar" style="object-fit: cover; width: 80px; height: 80px;">
                        <?php else: ?>
                            <div class="candidate-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <div class="candidate-name"><?php echo htmlspecialchars($candidate['name']); ?></div>
                        <?php if (!empty($candidate['description'])): ?>
                            <div style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($candidate['description']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!$user['has_voted']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                <button type="submit" name="vote" class="vote-btn" 
                                        onclick="return confirm('Vote for <?php echo htmlspecialchars($candidate['name']); ?>?')">
                                    <i class="fas fa-vote-yea"></i> Vote Now
                                </button>
                            </form>
                        <?php else: ?>
                            <button class="vote-btn" disabled>
                                <i class="fas fa-check"></i> 
                                <?php echo $user_vote === $candidate['name'] ? 'Your Choice' : 'Voted'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($user['has_voted']): ?>
                <div class="reset-section">
                    <h4 style="margin-bottom: 1rem; color: #374151;">Want to change your vote?</h4>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="new_password" value="reset">
                        <button type="submit" name="delete_vote" class="reset-btn"
                                onclick="return confirm('Reset your vote? You can vote again after.')">
                            <i class="fas fa-redo"></i> Reset My Vote
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>