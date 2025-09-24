<?php
session_start();
include 'db.php';
 
$error = $success = '';
if ($_POST) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
 
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = 'All fields required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 chars.';
    } else {
        // Check duplicates
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email exists.';
        } else {
            // Hash and insert
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed, $role])) {
                $user_id = $pdo->lastInsertId();
                if ($role === 'freelancer') {
                    $stmt = $pdo->prepare("INSERT INTO freelancers (user_id) VALUES (?)");
                    $stmt->execute([$user_id]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO clients (user_id) VALUES (?)");
                    $stmt->execute([$user_id]);
                }
                $success = 'Signup successful! Redirecting...';
                echo '<script>setTimeout(() => { window.location.href = "login.php"; }, 1500);</script>';
            } else {
                $error = 'Signup failed.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Clean form design */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; animation: slideIn 0.5s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input, select { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; transition: border-color 0.3s; }
        input:focus, select:focus { border-color: #667eea; outline: none; }
        .btn { width: 100%; background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: transform 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .error { color: red; text-align: center; margin: 1rem 0; }
        .success { color: green; text-align: center; margin: 1rem 0; }
        .link { text-align: center; margin-top: 1rem; }
        .link a { color: #667eea; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <?php if ($success) echo "<p class='success'>$success</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select</option>
                    <option value="freelancer">Freelancer</option>
                    <option value="client">Client</option>
                </select>
            </div>
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <div class="link">
            <a href="login.php">Already have account? Login</a>
        </div>
    </div>
 
    <script>
        // Internal JS - Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const pass = document.querySelector('input[name="password"]').value;
                if (pass.length < 6) {
                    e.preventDefault();
                    alert('Password too short!');
                }
            });
        });
    </script>
</body>
</html>
