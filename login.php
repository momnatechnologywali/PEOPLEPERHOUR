<?php
session_start();
include 'db.php';
 
if (isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "index.php";</script>';
    exit;
}
 
$error = '';
if ($_POST) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        echo '<script>window.location.href = "index.php";</script>';
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PeoplePerHour Clone</title>
    <style>
        /* Reuse similar CSS for consistency */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .form-container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; }
        input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; transition: border-color 0.3s; }
        input:focus { border-color: #667eea; outline: none; }
        .btn { width: 100%; background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; transition: transform 0.3s; }
        .btn:hover { transform: translateY(-2px); }
        .error { color: red; text-align: center; margin: 1rem 0; background: #ffe6e6; padding: 0.5rem; border-radius: 5px; }
        .link { text-align: center; margin-top: 1rem; }
        .link a { color: #667eea; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="link">
            <a href="signup.php">No account? Sign Up</a>
        </div>
    </div>
 
    <script>
        // Internal JS - Focus on email
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="email"]').focus();
        });
    </script>
</body>
</html>
