<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'freelancer') {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$freelancer_id = $pdo->query("SELECT id FROM freelancers WHERE user_id = " . $_SESSION['user_id'])->fetchColumn();
$job_id = (int)$_GET['job_id'];
 
if (!$freelancer_id || !$job_id) {
    echo '<script>window.location.href = "browse_jobs.php";</script>';
    exit;
}
 
// Fetch job
$stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND status = 'open'");
$stmt->execute([$job_id]);
$job = $stmt->fetch();
if (!$job) {
    echo '<script>window.location.href = "browse_jobs.php";</script>';
    exit;
}
 
// Check if already proposed
$stmt = $pdo->prepare("SELECT id FROM proposals WHERE job_id = ? AND freelancer_id = ?");
$stmt->execute([$job_id, $freelancer_id]);
if ($stmt->rowCount() > 0) {
    $error = 'Already submitted a proposal.';
}
 
if ($_POST) {
    $proposal_text = trim($_POST['proposal_text']);
    $bid_amount = $_POST['bid_amount'];
 
    if (empty($proposal_text) || empty($bid_amount)) {
        $error = 'All fields required.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO proposals (job_id, freelancer_id, proposal_text, bid_amount) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$job_id, $freelancer_id, $proposal_text, $bid_amount])) {
            $success = 'Proposal submitted! Redirecting...';
            echo '<script>setTimeout(() => { window.location.href = "my_proposals.php"; }, 1500);</script>';
        } else {
            $error = 'Submission failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Proposal - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Similar to post job */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 2rem; }
        h2 { text-align: center; margin-bottom: 1rem; color: #333; }
        .job-details { background: #f9f9f9; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; }
        textarea { height: 120px; resize: vertical; }
        .btn { background: #667eea; color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; width: 100%; }
        .btn:hover { background: #5a67d8; }
        .error, .success { text-align: center; margin: 1rem 0; padding: 0.5rem; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; }
        .success { color: green; background: #e6ffe6; }
        @media (max-width: 768px) { .container { padding: 1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Submit Proposal for: <?php echo htmlspecialchars($job['title']); ?></h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <div class="job-details">
            <p><strong>Description:</strong> <?php echo htmlspecialchars($job['description']); ?></p>
            <p><strong>Budget:</strong> $<?php echo $job['budget']; ?> | <strong>Deadline:</strong> <?php echo $job['deadline']; ?></p>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Your Proposal</label>
                <textarea name="proposal_text" placeholder="Describe how you'll complete this job..." required></textarea>
            </div>
            <div class="form-group">
                <label>Your Bid ($)</label>
                <input type="number" name="bid_amount" step="0.01" placeholder="e.g., 300" required>
            </div>
            <button type="submit" class="btn">Submit Proposal</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;"><a href="browse_jobs.php" style="color: #667eea;">Back to Jobs</a></p>
    </div>
 
    <script>
        // Internal JS - Bid validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const bidInput = document.querySelector('input[name="bid_amount"]');
            form.addEventListener('submit', function(e) {
                const bid = parseFloat(bidInput.value);
                if (bid <= 0) {
                    e.preventDefault();
                    alert('Bid must be positive!');
                }
            });
        });
    </script>
</body>
</html>
