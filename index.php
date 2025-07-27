<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class 4SK2 Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea, #764ba2); min-height: 100vh; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 1rem 0; margin-bottom: 2rem; border-radius: 12px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; font-size: 1.5rem; font-weight: bold; text-decoration: none; }
        .hero { text-align: center; color: white; margin: 3rem 0; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .card { background: rgba(255,255,255,0.95); border-radius: 16px; padding: 2rem; margin: 1rem 0; box-shadow: 0 8px 32px rgba(0,0,0,0.1); }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: bold; transition: transform 0.3s; cursor: pointer; }
        .btn-primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn:hover { transform: translateY(-2px); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav">
                <a href="index.php" class="logo"><i class="fas fa-trophy"></i> 4SK2 Voting</a>
                <div>
                    <a href="results.php" class="btn btn-primary">Results</a>
                    <a href="login.php" class="btn btn-primary">Login</a>
                </div>
            </nav>
        </header>

        <section class="hero">
            <h1>🎭 Class 4SK2 Impersonator Contest</h1>
            <p>Vote for the most talented impersonator!</p>
            <div style="margin-top: 2rem;">
                <a href="register.php" class="btn btn-primary" style="font-size: 1.1rem; margin: 0.5rem;">
                    <i class="fas fa-user-plus"></i> Join Voting
                </a>
                <a href="login.php" class="btn btn-primary" style="font-size: 1.1rem; margin: 0.5rem;">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            </div>
        </section>

        <div class="grid">
            <div class="card">
                <h3><i class="fas fa-users" style="color: #667eea;"></i> 21 Contestants</h3>
                <p>All Class 4SK2 students ready to showcase their impersonation skills!</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-vote-yea" style="color: #10b981;"></i> Secure Voting</h3>
                <p>One vote per person with advanced security measures.</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-chart-bar" style="color: #f59e0b;"></i> Live Results</h3>
                <p>Real-time vote counting with dynamic charts.</p>
            </div>
        </div>
    </div>
</body>
</html>