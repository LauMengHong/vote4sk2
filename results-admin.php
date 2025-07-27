<?php
require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) { header('Location: login.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY votes DESC, name");
$stmt->execute();
$candidates = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes");
$stmt->execute();
$total_votes = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Results - Class 4SK2</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 1rem 0; margin-bottom: 2rem; border-radius: 12px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; position: relative; overflow: hidden; }
        .logo::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent); animation: shimmer 3s infinite; }
        .nav-links a { color: white; text-decoration: none; margin-left: 1rem; padding: 0.5rem 1rem; border-radius: 8px; background: rgba(255,255,255,0.2); }
        .card { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 2rem; margin: 1rem 0; box-shadow: 0 8px 32px rgba(0,0,0,0.1); transition: all 0.4s ease; animation: fadeInUp 0.8s ease-out; }
        .card:hover { transform: translateY(-5px) rotateX(2deg); box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
        .hero { text-align: center; color: white; margin: 2rem 0; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; transition: all 0.3s ease; position: relative; overflow: hidden; animation: bounceIn 0.8s ease-out; }
        .stat-card::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.2), transparent); animation: rotate 4s linear infinite; opacity: 0; }
        .stat-card:hover::before { opacity: 1; }
        .stat-card:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 15px 40px rgba(0,0,0,0.2); }
        .stat-number { font-size: 2rem; font-weight: bold; display: block; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; transition: all 0.3s ease; }
        .table tbody tr { animation: fadeInLeft 0.6s ease-out; animation-fill-mode: both; }
        .table tr:hover { background: linear-gradient(90deg, #f8fafc, #f1f5f9, #f8fafc); transform: scale(1.01) translateX(5px); box-shadow: 0 4px 15px rgba(102,126,234,0.2); border-left: 4px solid #667eea; }
        .table th { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .winner { background: linear-gradient(135deg, #fff3cd, #fde68a) !important; animation: pulse 2s ease-in-out infinite; }
        .chart-container { background: white; padding: 2rem; border-radius: 12px; margin: 2rem 0; animation: glow 2s ease-in-out infinite; position: relative; overflow: hidden; }
        .chart-container::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: linear-gradient(45deg, transparent, rgba(102,126,234,0.1), transparent); animation: sweep 3s linear infinite; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounceIn { 0% { transform: scale(0.3); opacity: 0; } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { transform: scale(1); opacity: 1; } }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes rotate { to { transform: rotate(360deg); } }
        @keyframes glow { 0%, 100% { box-shadow: 0 0 5px rgba(102,126,234,0.3); } 50% { box-shadow: 0 0 20px rgba(102,126,234,0.6), 0 0 30px rgba(102,126,234,0.4); } }
        @keyframes sweep { 0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); } 100% { transform: translateX(100%) translateY(100%) rotate(45deg); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.02); } }
        @keyframes shimmer { 0% { left: -100%; } 100% { left: 100%; } }
        .floating { animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-5px); } }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav">
                <a href="admin.php" class="logo"><i class="fas fa-chart-bar floating"></i> Admin Results</a>
                <div class="nav-links">
                    <span style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                    <a href="admin.php">Admin Panel</a>
                    <a href="user_management.php">User Management</a>
                    <a href="logout.php">Logout</a>
                </div>
            </nav>
        </header>

        <div class="hero">
            <h1 class="floating">🏆 Contest Results</h1>
            <p>Class 4SK2 Impersonator Competition - Admin View</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo count($candidates); ?></span>
                <span>Contestants</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_votes; ?></span>
                <span>Total Votes</span>
            </div>
            <?php if ($total_votes > 0): ?>
            <div class="stat-card" style="background: linear-gradient(135deg, #ffd700, #ffed4e); color: #b45309;">
                <span class="stat-number"><i class="fas fa-crown"></i></span>
                <span>Winner: <?php echo htmlspecialchars($candidates[0]['name']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($total_votes > 0): ?>
        <div class="chart-container">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-chart-pie floating"></i> Voting Results Chart</h3>
            <canvas id="resultsChart" width="400" height="200"></canvas>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-trophy floating"></i> Complete Results</h3>
            <?php if ($total_votes == 0): ?>
                <div style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i class="fas fa-vote-yea" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <h4>No votes cast yet</h4>
                    <p>Waiting for votes...</p>
                </div>
            <?php else: ?>
                <table class="table">
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
                            <tr <?php echo $index === 0 && $candidate['votes'] > 0 ? 'class="winner"' : ''; ?>>
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
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#a8edea', '#d299c2']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Vote Distribution (Top 10)' }
                }
            }
        });
    </script>
    <?php endif; ?>
    
    <script>
        // Add staggered animation to stat cards
        document.querySelectorAll('.stat-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
        });
        
        // Add staggered animation to table rows
        document.querySelectorAll('.table tbody tr').forEach((row, index) => {
            row.style.animationDelay = `${index * 0.1}s`;
        });
        
        // Add hover effects to navigation links
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.05)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.2)';
            });
            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>