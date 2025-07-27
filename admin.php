<?php
require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) { header('Location: login.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC");
$stmt->execute();
$candidates = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes");
$stmt->execute();
$total_votes = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
$stmt->execute();
$total_users = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND has_voted = 1");
$stmt->execute();
$voted_users = $stmt->fetchColumn();

if ($_POST && isset($_POST['reset_votes'])) {
    $pdo->exec("DELETE FROM votes");
    $pdo->exec("UPDATE candidates SET votes = 0");
    $pdo->exec("UPDATE users SET has_voted = 0");
    header('Location: admin.php');
    exit();
}

if ($_POST && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    $pdo->exec("DELETE FROM votes WHERE user_id = $user_id");
    $pdo->exec("DELETE FROM users WHERE id = $user_id AND role = 'user'");
    header('Location: admin.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Class 4SK2</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 1rem 0; margin-bottom: 2rem; border-radius: 12px; animation: slideDown 0.8s ease-out; }
        @keyframes slideDown { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; }
        .nav-links a { color: white; text-decoration: none; margin-left: 1rem; padding: 0.5rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.2); transition: all 0.3s ease; }
        .nav-links a:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px) scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
        .card { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 2rem; margin: 1rem 0; box-shadow: 0 8px 32px rgba(0,0,0,0.1); transition: all 0.4s ease; }
        .card:hover { transform: translateY(-5px) rotateX(2deg); box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stats .stat-card { animation: bounceIn 0.8s ease-out; animation-fill-mode: both; }
        @keyframes bounceIn { 0% { transform: scale(0.3); opacity: 0; } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { transform: scale(1); opacity: 1; } }
        .stat-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; }
        .stat-number { font-size: 2rem; font-weight: bold; display: block; animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        @keyframes wiggle { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(1deg); } 75% { transform: rotate(-1deg); } }
        .logo:hover { animation: wiggle 0.5s ease-in-out; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-2px); } 75% { transform: translateX(2px); } }
        .btn:active { animation: shake 0.3s ease-in-out; }
        .fas { transition: all 0.3s ease; }
        .fas:hover { transform: scale(1.2) rotate(10deg); color: #ffd700; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin: 0.5rem; text-decoration: none; display: inline-block; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-info { background: #3b82f6; color: white; }
        .btn { margin: 0.25rem; }
        .table { font-size: 0.9rem; }
        .table { width: 100%; border-collapse: collapse; }
        .table tbody tr { transition: all 0.3s ease; animation: fadeInLeft 0.6s ease-out; animation-fill-mode: both; }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .table th { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .chart-container { background: white; padding: 2rem; border-radius: 12px; margin: 2rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav">
                <a href="admin.php" class="logo"><i class="fas fa-shield-alt"></i> Admin Panel</a>
                <div class="nav-links">
                    <span style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                    <a href="results-admin.php">Results</a>
                    <a href="user_management.php">User Management</a>
                    <a href="update_contestants.php">Update Contestants</a>
                    <a href="logout.php">Logout</a>
                </div>
            </nav>
        </header>

        <div class="stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($candidates); ?></span>
                <span>Contestants</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_votes; ?></span>
                <span>Total Votes</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_users; ?></span>
                <span>Total Users</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $voted_users; ?>/<?php echo $total_users; ?></span>
                <span>Users Voted</span>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 1rem;">Admin Controls</h2>
            <form method="POST" style="display: inline;">
                <button type="submit" name="reset_votes" class="btn btn-danger" 
                        onclick="return confirm('Reset all votes?')">
                    <i class="fas fa-redo"></i> Reset All Votes
                </button>
            </form>
            <a href="export.php" class="btn btn-info">
                <i class="fas fa-download"></i> Export Results
            </a>
        </div>

        <?php if ($total_votes > 0): ?>
        <div class="chart-container">
            <h3 style="margin-bottom: 1rem;">Live Voting Results</h3>
            <canvas id="adminChart" width="400" height="200"></canvas>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem;">Contestants & Vote Counts</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
                        <th>Votes</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidates as $index => $candidate): ?>
                        <tr>
                            <td>
                                <?php if ($index === 0 && $candidate['votes'] > 0): ?>
                                    <i class="fas fa-crown" style="color: #ffd700;"></i> #<?php echo $index + 1; ?>
                                <?php else: ?>
                                    #<?php echo $index + 1; ?>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($candidate['name']); ?></strong></td>
                            <td><?php echo $candidate['votes']; ?></td>
                            <td>
                                <?php 
                                $percentage = $total_votes > 0 ? round(($candidate['votes'] / $total_votes) * 100, 1) : 0;
                                echo $percentage . '%';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_votes > 0): ?>
    <script>
        const ctx = document.getElementById('adminChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($c) { return $c['name']; }, $candidates)); ?>,
                datasets: [{
                    label: 'Votes',
                    data: <?php echo json_encode(array_map(function($c) { return $c['votes']; }, $candidates)); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)'
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>