<?php
require_once 'config.php';

$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC, name");
$stmt->execute();
$candidates = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes");
$stmt->execute();
$total_votes = $stmt->fetchColumn();

// Refresh candidates to get updated vote counts
$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC, name");
$stmt->execute();
$candidates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results - Class 4SK2 Voting</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/animations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .results-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px;
        }
        .results-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 1rem 0;
            margin-bottom: 1.5rem;
            border-radius: 12px;
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
        .hero-section {
            text-align: center;
            color: white;
            margin: 1rem 0;
            padding: 1.5rem;
        }
        .hero-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-subtitle {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
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
            font-size: 1rem;
            font-weight: 500;
        }
        .results-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .results-table th,
        .results-table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .results-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0;
        }
        .results-table tr:hover {
            background: #f8fafc;
            transform: scale(1.01);
        }
        .winner-row {
            background: linear-gradient(135deg, #fef3c7, #fde68a) !important;
            font-weight: bold;
        }
        .rank-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .rank-1 {
            background: linear-gradient(135deg, #ffd700, #ffed4e);
            color: #b45309;
        }
        .rank-2 {
            background: linear-gradient(135deg, #c0c0c0, #e5e5e5);
            color: #374151;
        }
        .rank-3 {
            background: linear-gradient(135deg, #cd7f32, #daa520);
            color: white;
        }
        .no-votes {
            text-align: center;
            padding: 4rem;
            color: #6b7280;
        }
        .no-votes i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body>
    <div class="results-container">
        <header class="results-header">
            <nav class="nav">
                <a href="index.html" class="logo">
                    <i class="fas fa-trophy"></i> 4SK2 Results
                </a>
                <div class="nav-links">
                    <?php if (isLoggedIn()): ?>
                        <span style="color: white; margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="login.php">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>

        <div class="hero-section">
            <h1 class="hero-title">🏆 Contest Results</h1>
            <p class="hero-subtitle">Class 4SK2 Impersonator Competition Live Results</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($candidates); ?></span>
                <span class="stat-label">Total Contestants</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_votes; ?></span>
                <span class="stat-label">Votes Cast</span>
            </div>
            <?php if ($total_votes > 0): ?>
            <div class="stat-card">
                <span class="stat-number">🎭</span>
                <span class="stat-label">Winner: <?php echo htmlspecialchars($candidates[0]['name']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($total_votes > 0): ?>
        <div class="chart-container">
            <h3 style="margin-bottom: 1.5rem; color: #374151; font-size: 1.5rem;">Vote Distribution</h3>
            <canvas id="resultsChart" width="400" height="150"></canvas>
        </div>
        <?php endif; ?>

        <div class="results-card">
            <h3 style="margin-bottom: 1.5rem; color: #374151; font-size: 1.5rem;">
                <i class="fas fa-list-ol"></i> Complete Rankings
            </h3>
            
            <?php if ($total_votes == 0): ?>
                <div class="no-votes">
                    <i class="fas fa-vote-yea"></i>
                    <h4>No votes cast yet</h4>
                    <p>Be the first to vote and see the results!</p>
                </div>
            <?php else: ?>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Contestant</th>
                            <th>Votes</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $index => $candidate): ?>
                            <tr <?php echo $index === 0 && $candidate['votes'] > 0 ? 'class="winner-row"' : ''; ?>>
                                <td>
                                    <?php if ($index === 0 && $candidate['votes'] > 0): ?>
                                        <span class="rank-badge rank-1">
                                            <i class="fas fa-crown"></i> #<?php echo $index + 1; ?>
                                        </span>
                                    <?php elseif ($index === 1 && $candidate['votes'] > 0): ?>
                                        <span class="rank-badge rank-2">
                                            <i class="fas fa-medal"></i> #<?php echo $index + 1; ?>
                                        </span>
                                    <?php elseif ($index === 2 && $candidate['votes'] > 0): ?>
                                        <span class="rank-badge rank-3">
                                            <i class="fas fa-award"></i> #<?php echo $index + 1; ?>
                                        </span>
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
            <?php endif; ?>
        </div>
    </div>

    <?php if ($total_votes > 0): ?>
    <script>
        const ctx = document.getElementById('resultsChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_map(function($c) { return $c['name']; }, array_slice($candidates, 0, 10))); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function($c) { return $c['votes']; }, array_slice($candidates, 0, 10))); ?>,
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', 
                        '#43e97b', '#fa709a', '#fee140', '#a8edea', '#d299c2'
                    ],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>

    <script src="js/main.js"></script>
</body>
</html>