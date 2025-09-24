<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$client_id = $pdo->query("SELECT id FROM clients WHERE user_id = " . $_SESSION['user_id'])->fetchColumn();
 
if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $budget = $_POST['budget'];
    $deadline = $_POST['deadline'];
    $category = $_POST['category'];
    $location = $_POST['location'];
 
    $stmt = $pdo->prepare("INSERT INTO jobs (client_id, title, description, budget, deadline, category, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$client_id, $title, $description, $budget, $deadline, $category, $location])) {
        $success = 'Job posted! Redirecting...';
        echo '<script>setTimeout(() => { window.location.href = "my_jobs.php"; }, 1500);</script>';
    } else {
        $error = 'Posting failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Job - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Form heavy */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; padding: 2rem; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); padding: 2rem; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; }
        textarea { resize: vertical; height: 100px; }
        .btn { background: #667eea; color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1rem; transition: background 0.3s; }
        .btn:hover { background: #5a67d8; }
        .error { color: red; text-align: center; margin: 1rem 0; }
        .success { color: green; text-align: center; margin: 1rem 0; }
        nav { text-align: center; margin-bottom: 1rem; }
        nav a { color: #667eea; text-decoration: none; margin: 0 1rem; }
        @media (max-width: 768px) { .container { padding: 1rem; margin: 1rem; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
        <a href="my_jobs.php">My Jobs</a>
    </nav>
    <div class="container">
        <h2>Post a New Job</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="form-group">
                <label>Budget ($)</label>
                <input type="number" name="budget" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Deadline</label>
                <input type="date" name="deadline" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="Web Dev">Web Development</option>
                    <option value="Design">Design</option>
                    <option value="Writing">Writing</option>
                    <option value="Marketing">Marketing</option>
                    <option value="General">General</option>
                </select>
            </div>
            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="Remote" required>
            </div>
            <button type="submit" class="btn">Post Job</button>
        </form>
    </div>
 
    <script>
        // Internal JS - Date min today
        document.addEventListener('DOMContentLoaded', function() {
            const deadline = document.querySelector('input[name="deadline"]');
            const today = new Date().toISOString().split('T')[0];
            deadline.min = today;
        });
    </script>
</body>
</html>
