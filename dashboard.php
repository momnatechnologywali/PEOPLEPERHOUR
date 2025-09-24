<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
 
// Quick stats
if ($role === 'freelancer') {
    $stmt = $pdo->prepare("SELECT COUNT(*) as proposals, AVG(rating) as avg_rating FROM proposals p LEFT JOIN freelancers f ON p.freelancer_id = f.id WHERE f.user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) as jobs FROM jobs WHERE client_id = (SELECT id FROM clients WHERE user_id = ?)");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch();
    $stats['jobs'] = $stats['COUNT(*)'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Stats cards */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 1200px; margin: 0 auto; }
        h2 { margin-bottom: 1rem; color: #333; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 2rem; text-align: center; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 2.5rem; color: #667eea; font-weight: bold; }
        nav { text-align: center; margin-bottom: 2rem; }
        nav a { color: #667eea; text-decoration: none; margin: 0 1rem; }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="profile.php">Profile</a>
        <a href="messages.php">Messages</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="container">
        <h2><?php echo ucfirst($role); ?> Dashboard</h2>
        <div class="stats-grid">
            <?php if ($role === 'freelancer') { ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['proposals']; ?></div>
                    <p>Total Proposals</p>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                    <p>Avg Rating</p>
                </div>
                <div class="stat-card">
                    <a href="my_proposals.php" style="color: inherit; text-decoration: none;">View Proposals</a>
                </div>
            <?php } else { ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['jobs']; ?></div>
                    <p>Total Jobs Posted</p>
                </div>
                <div class="stat-card">
                    <a href="my_jobs.php" style="color: inherit; text-decoration: none;">Manage Jobs</a>
                </div>
                <div class="stat-card">
                    <a href="post_job.php" style="color: inherit; text-decoration: none;">Post New Job</a>
                </div>
            <?php } ?>
        </div>
        <p style="text-align: center; color: #666;">Quick links above. Everything working smoothly!</p>
    </div>
 
    <script>
        // Internal JS - Animate numbers (simple)
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 200);
            });
        });
    </script>
</body>
</html>
