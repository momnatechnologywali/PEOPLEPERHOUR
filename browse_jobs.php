<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
// Filters
$where = "status = 'open'";
$params = [];
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where .= " AND category = ?";
    $params[] = $_GET['category'];
}
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where .= " AND (title LIKE ? OR description LIKE ?)";
    $search = "%" . $_GET['search'] . "%";
    $params[] = $search;
    $params[] = $search;
}
if (isset($_GET['budget_min'])) {
    $where .= " AND budget >= ?";
    $params[] = $_GET['budget_min'];
}
if (isset($_GET['location']) && $_GET['location'] !== 'All') {
    $where .= " AND location = ?";
    $params[] = $_GET['location'];
}
 
$sql = "SELECT * FROM jobs WHERE $where ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Jobs - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Grid for jobs */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        h2 { margin-bottom: 1rem; color: #333; }
        .filters { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .filters form { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; }
        input, select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { background: #667eea; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; }
        .job-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .job-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: box-shadow 0.3s; }
        .job-card:hover { box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .job-title { font-size: 1.2rem; margin-bottom: 0.5rem; color: #333; }
        .job-meta { color: #666; margin-bottom: 0.5rem; }
        nav { text-align: center; padding: 1rem; background: #f9f9f9; }
        nav a { color: #667eea; text-decoration: none; margin: 0 1rem; }
        @media (max-width: 768px) { .filters form { flex-direction: column; align-items: stretch; } .job-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="my_proposals.php">My Proposals</a>
    </nav>
    <div class="container">
        <h2>Browse Open Jobs</h2>
        <div class="filters">
            <form method="GET">
                <input type="text" name="search" placeholder="Search jobs..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Web Dev" <?php echo ($_GET['category'] ?? '') === 'Web Dev' ? 'selected' : ''; ?>>Web Dev</option>
                    <option value="Design" <?php echo ($_GET['category'] ?? '') === 'Design' ? 'selected' : ''; ?>>Design</option>
                    <!-- Add more -->
                </select>
                <input type="number" name="budget_min" placeholder="Min Budget" step="0.01" value="<?php echo htmlspecialchars($_GET['budget_min'] ?? ''); ?>">
                <select name="location">
                    <option value="All">All Locations</option>
                    <option value="Remote" <?php echo ($_GET['location'] ?? '') === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                    <option value="Onsite">Onsite</option>
                </select>
                <button type="submit" class="btn">Filter</button>
            </form>
        </div>
        <div class="job-grid">
            <?php foreach ($jobs as $job) { ?>
                <div class="job-card">
                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($job['description'], 0, 100)); ?>...</p>
                    <p class="job-meta">Budget: $<?php echo $job['budget']; ?> | Deadline: <?php echo $job['deadline']; ?> | <?php echo $job['category']; ?> | <?php echo $job['location']; ?></p>
                    <a href="submit_proposal.php?job_id=<?php echo $job['id']; ?>" class="btn" style="font-size: 0.9rem; margin-top: 1rem; display: inline-block;">Submit Proposal</a>
                </div>
            <?php } ?>
            <?php if (empty($jobs)) { echo '<p style="text-align: center; color: #666;">No jobs found.</p>'; } ?>
        </div>
    </div>
 
    <script>
        // Internal JS - Real-time search simulation (client-side filter)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            searchInput.addEventListener('input', function() {
                // Could add client-side filter here if needed
            });
        });
    </script>
</body>
</html>
