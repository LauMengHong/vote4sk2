<?php
require_once 'config.php';
if (!isLoggedIn() || !isAdmin()) { header('Location: login.php'); exit(); }

$stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY name");
$stmt->execute();
$candidates = $stmt->fetchAll();

if ($_POST && isset($_POST['add_contestant'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    $stmt = $pdo->prepare("INSERT INTO candidates (name, description, votes) VALUES (?, ?, 0)");
    $stmt->execute([$name, $description]);
    $candidate_id = $pdo->lastInsertId();
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/candidates/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'candidate_' . $candidate_id . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("UPDATE candidates SET image = ? WHERE id = ?");
                $stmt->execute([$new_filename, $candidate_id]);
            }
        }
    }
    
    $success = "Contestant added successfully!";
    $stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY name");
    $stmt->execute();
    $candidates = $stmt->fetchAll();
}

if ($_POST && isset($_POST['update_contestant'])) {
    $candidate_id = (int)$_POST['candidate_id'];
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    
    $stmt = $pdo->prepare("UPDATE candidates SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $candidate_id]);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'images/candidates/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'candidate_' . $candidate_id . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $stmt = $pdo->prepare("UPDATE candidates SET image = ? WHERE id = ?");
                $stmt->execute([$new_filename, $candidate_id]);
            }
        }
    }
    
    $success = "Contestant updated successfully!";
    $stmt = $pdo->prepare("SELECT * FROM candidates ORDER BY name");
    $stmt->execute();
    $candidates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Contestants - Class 4SK2</title>
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
        .update-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 15px;
        }
        .update-header {
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 15px;
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
        .update-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .add-section {
            background: rgba(59, 130, 246, 0.1);
            border: 2px dashed rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
            animation: slideInDown 0.8s ease-out;
            transition: all 0.3s ease;
        }
        .add-section:hover {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }
        .add-form {
            max-width: 400px;
            margin: 0 auto;
            text-align: left;
        }
        .contestants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .contestant-card {
            background: white;
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid transparent;
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }
        .contestant-card:hover {
            transform: translateY(-5px) rotateY(2deg);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            border-color: rgba(102, 126, 234, 0.4);
        }
        .contestant-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            object-fit: cover;
            border: 3px solid #667eea;
            transition: all 0.3s ease;
        }
        .contestant-image:hover {
            transform: scale(1.1) rotateY(10deg);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .placeholder-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 1.8rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .placeholder-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: rotate 3s linear infinite;
            opacity: 0;
        }
        .placeholder-avatar:hover::before {
            opacity: 1;
        }
        .placeholder-avatar:hover {
            transform: scale(1.1) rotateY(10deg);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.3rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }
        .form-input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.1);
        }
        .form-textarea {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            resize: vertical;
            min-height: 60px;
        }
        .update-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            width: 100%;
            margin-top: 0.5rem;
        }
        .update-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .add-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            font-size: 1rem;
        }
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid;
            background: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border-color: rgba(16, 185, 129, 0.3);
        }
        .file-input-wrapper {
            position: relative;
        }
        .file-input-hidden {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .file-input-button {
            display: inline-block;
            padding: 0.6rem 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
        }
        .file-input-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .file-name {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6b7280;
            font-style: italic;
        }
        .section-title {
            color: #374151;
            font-size: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .section-subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .add-title {
            color: #0369a1;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes rotate {
            to { transform: rotate(360deg); }
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .pulse-on-success {
            animation: pulse 0.6s ease-in-out;
        }
    </style>
</head>
<body>
    <div class="update-container">
        <header class="update-header">
            <nav class="nav">
                <a href="update_contestants.php" class="logo">
                    <i class="fas fa-user-edit"></i> Update Contestants
                </a>
                <div class="nav-links">
                    <a href="admin.php"><i class="fas fa-shield-alt"></i> Admin Panel</a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </header>

        <div class="update-card">
            <h2 class="section-title">
                <i class="fas fa-user-edit"></i> Manage Contestants
            </h2>
            <p class="section-subtitle">
                Add new contestants or update existing profiles with photos and descriptions
            </p>

            <?php if (isset($success)): ?>
                <div class="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="add-section">
                <h3 class="add-title">
                    <i class="fas fa-plus-circle"></i> Add New Contestant
                </h3>
                <form method="POST" enctype="multipart/form-data" class="add-form">
                    <div class="form-group">
                        <label class="form-label">Name:</label>
                        <input type="text" name="name" class="form-input" required placeholder="Enter contestant name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description:</label>
                        <textarea name="description" class="form-textarea" placeholder="Enter description (optional)"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Photo (optional):</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="image" accept="image/*" id="file-new" class="file-input-hidden">
                            <label for="file-new" class="file-input-button">
                                <i class="fas fa-camera"></i> Choose Photo
                            </label>
                            <span class="file-name" id="filename-new">No file chosen</span>
                        </div>
                    </div>
                    <button type="submit" name="add_contestant" class="add-btn">
                        <i class="fas fa-plus"></i> Add Contestant
                    </button>
                </form>
            </div>

            <div class="contestants-grid">
                <?php foreach ($candidates as $index => $candidate): ?>
                    <div class="contestant-card" style="animation-delay: <?php echo ($index * 0.1); ?>s;">
                        <?php if (!empty($candidate['image']) && file_exists('images/candidates/' . $candidate['image'])): ?>
                            <img src="images/candidates/<?php echo htmlspecialchars($candidate['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                 class="contestant-image">
                        <?php else: ?>
                            <div class="placeholder-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                            
                            <div class="form-group">
                                <label class="form-label">Name:</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($candidate['name']); ?>" 
                                       class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Description:</label>
                                <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($candidate['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Photo (optional):</label>
                                <div class="file-input-wrapper">
                                    <input type="file" name="image" accept="image/*" id="file-<?php echo $candidate['id']; ?>" class="file-input-hidden">
                                    <label for="file-<?php echo $candidate['id']; ?>" class="file-input-button">
                                        <i class="fas fa-camera"></i> Choose Photo
                                    </label>
                                    <span class="file-name" id="filename-<?php echo $candidate['id']; ?>">No file chosen</span>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_contestant" class="update-btn">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.file-input-hidden').forEach(input => {
            input.addEventListener('change', function() {
                const filename = this.files[0] ? this.files[0].name : 'No file chosen';
                const fileId = this.id === 'file-new' ? 'filename-new' : 'filename-' + this.id.split('-')[1];
                document.getElementById(fileId).textContent = filename;
            });
        });
        
        // Add success animation
        <?php if (isset($success)): ?>
        document.querySelector('.alert').classList.add('pulse-on-success');
        <?php endif; ?>
        
        // Add button click animations
        document.querySelectorAll('.update-btn, .add-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>