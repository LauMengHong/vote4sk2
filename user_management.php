<?php
require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) { header('Location: login.php'); exit(); }

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
$stmt->execute();
$total_users = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND has_voted = 1");
$stmt->execute();
$voted_users = $stmt->fetchColumn();

if ($_POST && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    $pdo->exec("DELETE FROM votes WHERE user_id = $user_id");
    $pdo->exec("DELETE FROM users WHERE id = $user_id AND role = 'user'");
    header('Location: user_management.php');
    exit();
}

if ($_POST && isset($_POST['reset_password']) && isset($_POST['new_password'])) {
    $user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'user'");
    $stmt->execute([$hashed_password, $user_id]);
    $success = "Password changed successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Class 4SK2</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .btn { padding: 0.5rem 1rem; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: all 0.3s ease; position: relative; overflow: hidden; }
        .btn::after { content: ''; position: absolute; top: 50%; left: 50%; width: 0; height: 0; background: rgba(255,255,255,0.3); border-radius: 50%; transform: translate(-50%, -50%); transition: width 0.6s, height 0.6s; }
        .btn:hover::after { width: 300px; height: 300px; }
        .btn:hover { transform: translateY(-2px) scale(1.05); box-shadow: 0 8px 20px rgba(0,0,0,0.3); }
        .btn-danger { background: #ef4444; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 8px; background: #d1fae5; color: #065f46; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes bounceIn { 0% { transform: scale(0.3); opacity: 0; } 50% { transform: scale(1.05); } 70% { transform: scale(0.9); } 100% { transform: scale(1); opacity: 1; } }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes rotate { to { transform: rotate(360deg); } }
        @keyframes shimmer { 0% { left: -100%; } 100% { left: 100%; } }
        .floating { animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-5px); } }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <nav class="nav">
                <a href="admin.php" class="logo"><i class="fas fa-users-cog floating"></i> User Management</a>
                <div class="nav-links">
                    <span style="color: white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
                    <a href="admin.php">Admin Dashboard</a>

                    <a href="logout.php">Logout</a>
                </div>
            </nav>
        </header>

        <div class="stats">
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_users; ?></span>
                <span>Total Users</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $voted_users; ?></span>
                <span>Users Voted</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $total_users - $voted_users; ?></span>
                <span>Not Voted</span>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-users floating"></i> Registered Users</h3>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Voted</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
                        $stmt->execute();
                        $users = $stmt->fetchAll();
                        foreach ($users as $user): 
                        ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo $user['has_voted'] ? '✅ Yes' : '❌ No'; ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button onclick="showPasswordModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')" class="btn btn-warning" style="margin-right: 0.5rem;">
                                        <i class="fas fa-key"></i> Change
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger"
                                                onclick="return confirm('Delete user <?php echo htmlspecialchars($user['name']); ?>?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 12px; width: 400px;">
            <h3 style="margin-bottom: 1rem;">Change Password</h3>
            <form method="POST">
                <input type="hidden" id="modal_user_id" name="user_id">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">New Password:</label>
                    <input type="password" name="new_password" style="width: 100%; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px;" required minlength="6">
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" name="reset_password" class="btn btn-warning" style="flex: 1;">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button type="button" onclick="hidePasswordModal()" style="flex: 1; padding: 0.75rem; border: 2px solid #e5e7eb; border-radius: 8px; background: white; cursor: pointer;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showPasswordModal(userId, userName) {
            document.getElementById('modal_user_id').value = userId;
            document.getElementById('passwordModal').style.display = 'block';
        }
        
        function hidePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
        
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