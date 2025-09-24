<?php
session_start();
include 'db.php';
 
// If not logged in, redirect to login via JS
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
 
// Fetch featured freelancers (top rated)
$stmt = $pdo->query("
    SELECT u.username, f.skills, f.rating, f.hourly_rate 
    FROM users u 
    JOIN freelancers f ON u.id = f.user_id 
    ORDER BY f.rating DESC LIMIT 6
");
$freelancers = $stmt->fetchAll();
 
// Fetch categories (from jobs)
$stmt = $pdo->query("SELECT DISTINCT category FROM jobs WHERE status = 'open' LIMIT 8");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeoplePerHour Clone - Homepage</title>
    <style>
        /* Internal CSS - Professional, modern, responsive */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        header { background: rgba(255,255,255,0.95); padding: 1rem 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
        nav ul { display: flex; list-style: none; gap: 2rem; }
        nav a { text-decoration: none; color: #333; font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: #667eea; }
        .hero { text-align: center; padding: 4rem 2rem; color: white; }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; animation: fadeIn 1s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .categories { background: white; padding: 3rem 2rem; }
        .categories h2 { text-align: center; margin-bottom: 2rem; color: #333; }
        .cat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; max-width: 1200px; margin: 0 auto; }
        .cat-card { background: linear-gradient(45deg, #f0f0f0, #e0e0e0); padding: 1.5rem; text-align: center; border-radius: 10px; transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; }
        .cat-card:hover { transform: translateY(-5px); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .freelancers { padding: 3rem 2rem; background: #f9f9f9; }
        .freelancers h2 { text-align: center; margin-bottom: 2rem; color: #333; }
        .free-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto; }
        .free-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: all 0.3s; }
        .free-card:hover { box-shadow: 0 5px 20px rgba(0,0,0,0.1); transform: scale(1.02); }
        .free-card img { width: 60px; height: 60px; border-radius: 50%; background: #667eea; margin-bottom: 1rem; display: inline-block; }
        .btn { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; transition: opacity 0.3s; text-decoration: none; display: inline-block; }
        .btn:hover { opacity: 0.9; }
        footer { background: #333; color: white; text-align: center; padding: 2rem; }
        @media (max-width: 768px) { .hero h1 { font-size: 2rem; } nav ul { flex-direction: column; gap: 1rem; } .cat-grid, .free-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <nav>
            <h2>PeoplePerHour Clone</h2>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if ($role === 'client') { echo '<li><a href="post_job.php">Post Job</a></li>'; } else { echo '<li><a href="browse_jobs.php">Browse Jobs</a></li>'; } ?>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
 
    <section class="hero">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Connect with top talent or find amazing projects.</p>
        <?php if ($role === 'client') { ?><a href="post_job.php" class="btn">Post a Job</a><?php } else { ?><a href="browse_jobs.php" class="btn">Find Jobs</a><?php } ?>
    </section>
 
    <section class="categories">
        <h2>Popular Categories</h2>
        <div class="cat-grid">
            <?php foreach ($categories as $cat) { ?>
                <div class="cat-card" onclick="window.location.href='browse_jobs.php?category=<?php echo urlencode($cat['category']); ?>'">
                    <?php echo htmlspecialchars($cat['category']); ?>
                </div>
            <?php } ?>
        </div>
    </section>
 
    <section class="freelancers">
        <h2>Featured Freelancers</h2>
        <div class="free-grid">
            <?php foreach ($freelancers as $f) { ?>
                <div class="free-card">
                    <div style="background: #667eea;"></div> <!-- Placeholder avatar -->
                    <h3><?php echo htmlspecialchars($f['username']); ?></h3>
                    <p>Skills: <?php echo htmlspecialchars($f['skills']); ?></p>
                    <p>Rating: <?php echo $f['rating']; ?> | Rate: $<?php echo $f['hourly_rate']; ?>/hr</p>
                    <a href="profile.php?id=<?php echo $f['user_id'] ?? ''; ?>" class="btn" style="font-size: 0.9rem; margin-top: 0.5rem;">View Profile</a>
                </div>
            <?php } ?>
        </div>
    </section>
 
    <footer>
        <p>&copy; 2025 PeoplePerHour Clone. All rights reserved.</p>
    </footer>
 
    <script>
        // Internal JS - Simple interactions, no external
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            const cards = document.querySelectorAll('.cat-card, .free-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
