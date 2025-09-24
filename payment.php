<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$proposal_id = (int)$_GET['proposal_id'];
 
// Fetch proposal and amount
$stmt = $pdo->prepare("SELECT p.bid_amount, p.status, j.client_id FROM proposals p JOIN jobs j ON p.job_id = j.id WHERE p.id = ? AND p.status = 'accepted'");
$stmt->execute([$proposal_id]);
$prop = $stmt->fetch();
if (!$prop || $_SESSION['role'] !== 'client' || $prop['client_id'] != $pdo->query("SELECT id FROM clients WHERE user_id = " . $_SESSION['user_id'])->fetchColumn()) {
    echo '<script>window.location.href = "my_jobs.php";</script>';
    exit;
}
 
$amount = $prop['bid_amount'];
 
// Process payment if POST
if ($_POST) {
    $method = $_POST['method'];
    $notes = $_POST['notes'] ?? '';
 
    $stmt = $pdo->prepare("INSERT INTO payments (proposal_id, amount, method, notes) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$proposal_id, $amount, $method, $notes])) {
        $payment_id = $pdo->lastInsertId();
        $paid_at = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE payments SET status = 'paid', paid_at = ? WHERE id = ?");
        $stmt->execute([$paid_at, $payment_id]);
        if ($method === 'online') {
            $notes .= ' | Dummy TX ID: ' . uniqid();
        }
        $success = 'Payment processed via ' . ucfirst($method) . '! Notes: ' . $notes;
    } else {
        $error = 'Payment failed.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Payment form */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); width: 100%; max-width: 400px; }
        h2 { text-align: center; margin-bottom: 1rem; color: #333; }
        .amount { text-align: center; font-size: 2rem; color: #48bb78; margin: 1rem 0; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        select, input { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; background: #48bb78; color: white; padding: 1rem; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background 0.3s; margin-top: 1rem; }
        .btn:hover { background: #38a169; }
        .error, .success { text-align: center; margin: 1rem 0; padding: 0.5rem; border-radius: 5px; }
        .error { color: red; background: #ffe6e6; }
        .success { color: green; background: #e6ffe6; }
        @media (max-width: 480px) { .container { margin: 1rem; padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Milestone Payment</h2>
        <div class="amount">$<?php echo $amount; ?></div>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (!isset($success)) { ?>
            <form method="POST">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="method" required>
                        <option value="cod">Cash on Delivery</option>
                        <option value="online">Dummy Online Payment</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <input type="text" name="notes" placeholder="e.g., Milestone 1">
                </div>
                <button type="submit" class="btn">Process Payment</button>
            </form>
            <p style="text-align: center; margin-top: 1rem;"><a href="my_jobs.php" style="color: #667eea;">Back to Jobs</a></p>
        <?php } else { ?>
            <p style="text-align: center;"><a href="my_jobs.php" style="color: #667eea;">Back to Jobs</a></p>
        <?php } ?>
    </div>
 
    <script>
        // Internal JS - Method change effect
        document.addEventListener('DOMContentLoaded', function() {
            const select = document.querySelector('select[name="method"]');
            select.addEventListener('change', function() {
                const notes = document.querySelector('input[name="notes"]');
                if (this.value === 'online') {
                    notes.placeholder = 'e.g., Card details (dummy)';
                } else {
                    notes.placeholder = 'e.g., Delivery address';
                }
            });
        });
    </script>
</body>
</html>
