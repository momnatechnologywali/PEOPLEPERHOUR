<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$client_id = $pdo->query("SELECT id FROM clients WHERE user_id = " . $_SESSION['user_id'])->fetchColumn();
 
// Accept proposal if GET accept=proposal_id
if (isset($_GET['accept'])) {
    $prop_id = (int)$_GET['accept'];
    $stmt = $pdo->prepare("UPDATE proposals SET status = 'accepted' WHERE id = ?");
    $stmt->execute([$prop_id]);
    $stmt = $pdo->prepare("UPDATE jobs SET status = 'assigned' WHERE id = (SELECT job_id FROM proposals WHERE id = ?)");
    $stmt->execute([$prop_id]);
    echo '<script>window.location.href = "my_jobs.php";</script>';
    exit;
}
 
// Fetch jobs with proposals
$stmt = $pdo->prepare("
    SELECT j.*, COUNT(p.id) as proposal_count 
    FROM jobs j 
    LEFT JOIN proposals p ON j.id = p.job_id 
    WHERE j.client_id = ? 
    GROUP BY j.id 
    ORDER BY j.created_at DESC
");
$stmt->execute([$client_id]);
$jobs = $stmt->fetchAll();
 
// For each job, fetch proposals if needed (simple, fetch on click but here inline)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Jobs - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - List with expandable */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        h2 { margin-bottom: 1rem; color: #333; }
        .job-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .job-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .job-title { font-size: 1.2rem; margin-bottom: 0.5rem; }
        .proposals { margin-top: 1rem; }
        .proposal-item { background: #f9f9f9; padding: 1rem; margin: 0.5rem 0; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .btn { background: #667eea; color: white; padding: 0.5rem 1rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn.accept { background: #48bb78; }
        nav { text-align: center; padding: 1rem; background: #f9f9f9; }
        nav a { color: #667eea; text-decoration: none; margin: 0 1rem; }
        @media (max-width: 768px) { .job-grid { grid-template-columns: 1fr; } .proposal-item { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="post_job.php">Post New Job</a>
    </nav>
    <div class="container">
        <h2>My Jobs</h2>
        <div class="job-grid">
            <?php foreach ($jobs as $job) { ?>
                <div class="job-card">
                    <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                    <p>Status: <?php echo ucfirst($job['status']); ?> | Proposals: <?php echo $job['proposal_count']; ?></p>
                    <?php if ($job['status'] === 'open' && $job['proposal_count'] > 0) { ?>
                        <div class="proposals">
                            <?php
                            // Fetch proposals for this job
                            $stmt = $pdo->prepare("
                                SELECT p.*, u.username 
                                FROM proposals p 
                                JOIN freelancers f ON p.freelancer_id = f.id 
                                JOIN users u ON f.user_id = u.id 
                                WHERE p.job_id = ? AND p.status = 'pending'
                            ");
                            $stmt->execute([$job['id']]);
                            $props = $stmt->fetchAll();
                            foreach ($props as $prop) { ?>
                                <div class="proposal-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($prop['username']); ?>:</strong> <?php echo htmlspecialchars(substr($prop['proposal_text'], 0, 50)); ?>... | Bid: $<?php echo $prop['bid_amount']; ?>
                                    </div>
                                    <a href="?accept=<?php echo $prop['id']; ?>" class="btn accept" onclick="return confirm('Accept this proposal?')">Accept</a>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
 
    <script>
        // Internal JS - Confirm accept
        // Already in onclick, but add more if needed
    </script>
</body>
</html>
