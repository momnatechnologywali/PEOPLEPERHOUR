<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
 
// Fetch profile
if ($role === 'freelancer') {
    $stmt = $pdo->prepare("SELECT * FROM freelancers f JOIN users u ON f.user_id = u.id WHERE f.user_id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM clients c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
}
$stmt->execute([$user_id]);
$profile = $stmt->fetch();
 
// Update if POST
if ($_POST && isset($_POST['update'])) {
    $data = [
        'skills' => $_POST['skills'] ?? '',
        'experience' => $_POST['experience'] ?? '',
        'portfolio' => $_POST['portfolio'] ?? '',
        'business_name' => $_POST['business_name'] ?? '',
        'description' => $_POST['description'] ?? ''
    ];
    if ($role === 'freelancer') {
        $stmt = $pdo->prepare("UPDATE freelancers SET skills = ?, experience = ?, portfolio = ? WHERE user_id = ?");
        $stmt->execute([$data['skills'], $data['experience'], $data['portfolio'], $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE clients SET business_name = ?, description = ? WHERE user_id = ?");
        $stmt->execute([$data['business_name'], $data['description'], $user_id]);
    }
    $success = 'Profile updated!';
}
 
// View other profile if ?id=
$view_id = $_GET['id'] ?? $user_id;
if ($view_id != $user_id) {
    // Fetch other profile (read-only)
    if ($_SESSION['role'] === 'freelancer') {
        $stmt = $pdo->prepare("SELECT * FROM freelancers f JOIN users u ON f.user_id = u.id WHERE f.user_id = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM clients c JOIN users u ON c.user_id = u.id WHERE c.user_id = ?");
    }
    $stmt->execute([$view_id]);
    $profile = $stmt->fetch();
    $is_view = true;
} else {
    $is_view = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Profile card style */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); overflow: hidden; }
        header { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 2rem; text-align: center; }
        .profile-info { padding: 2rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        textarea, input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; resize: vertical; }
        .btn { background: #667eea; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; margin: 0.5rem; transition: background 0.3s; }
        .btn:hover { background: #5a67d8; }
        .success { color: green; text-align: center; margin: 1rem 0; }
        nav { background: #f9f9f9; padding: 1rem; text-align: center; }
        nav a { margin: 0 1rem; color: #667eea; text-decoration: none; }
        @media (max-width: 768px) { body { padding: 1rem; } .profile-info { padding: 1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($profile['username'] ?? 'Profile'); ?>'s <?php echo ucfirst($role); ?> Profile</h1>
            <?php if ($role === 'freelancer') { echo '<p>Rating: ' . ($profile['rating'] ?? 0) . '</p>'; } ?>
        </header>
        <nav>
            <a href="index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
        <div class="profile-info">
            <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
            <?php if (!$is_view) { ?>
                <form method="POST">
                    <?php if ($role === 'freelancer') { ?>
                        <div class="form-group">
                            <label>Skills (comma-separated)</label>
                            <textarea name="skills" rows="3"><?php echo htmlspecialchars($profile['skills'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Experience</label>
                            <textarea name="experience" rows="3"><?php echo htmlspecialchars($profile['experience'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Portfolio Links</label>
                            <textarea name="portfolio" rows="3"><?php echo htmlspecialchars($profile['portfolio'] ?? ''); ?></textarea>
                        </div>
                    <?php } else { ?>
                        <div class="form-group">
                            <label>Business Name</label>
                            <input type="text" name="business_name" value="<?php echo htmlspecialchars($profile['business_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3"><?php echo htmlspecialchars($profile['description'] ?? ''); ?></textarea>
                        </div>
                    <?php } ?>
                    <button type="submit" name="update" class="btn">Update Profile</button>
                </form>
            <?php } else { ?>
                <!-- View mode -->
                <p><strong>Skills/Info:</strong> <?php echo htmlspecialchars($profile['skills'] ?? $profile['business_name'] ?? 'N/A'); ?></p>
                <p><strong>Experience/Desc:</strong> <?php echo htmlspecialchars($profile['experience'] ?? $profile['description'] ?? 'N/A'); ?></p>
                <p><strong>Portfolio:</strong> <?php echo htmlspecialchars($profile['portfolio'] ?? 'N/A'); ?></p>
            <?php } ?>
        </div>
    </div>
 
    <script>
        // Internal JS - Auto-save preview or something simple
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    // Simulate loading
                    this.querySelector('button').innerText = 'Updating...';
                });
            }
        });
    </script>
</body>
</html>
