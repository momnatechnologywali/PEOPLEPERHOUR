<?php
session_start();
include 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.href = "login.php";</script>';
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Get conversations (users messaged with)
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username, m.timestamp 
    FROM users u 
    JOIN messages m ON (m.from_user_id = u.id OR m.to_user_id = u.id) 
    WHERE (m.from_user_id = ? OR m.to_user_id = ?) AND u.id != ? 
    ORDER BY m.timestamp DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();
 
// If ?to=other_user_id, load messages
$to_id = $_GET['to'] ?? null;
$messages = [];
if ($to_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, u.username as from_name 
        FROM messages m 
        JOIN users u ON m.from_user_id = u.id 
        WHERE (m.from_user_id = ? AND m.to_user_id = ?) OR (m.from_user_id = ? AND m.to_user_id = ?) 
        ORDER BY m.timestamp ASC
    ");
    $stmt->execute([$user_id, $to_id, $to_id, $user_id]);
    $messages = $stmt->fetchAll();
}
 
// Send message if POST
if ($_POST && isset($_POST['message']) && $to_id) {
    $message = trim($_POST['message']);
    $is_file = isset($_POST['is_file']) ? 1 : 0;
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (from_user_id, to_user_id, message, is_file) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $to_id, $message, $is_file]);
        echo '<script>window.location.href = "messages.php?to=' . $to_id . '";</script>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PeoplePerHour Clone</title>
    <style>
        /* Internal CSS - Chat layout */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; display: flex; height: 100vh; }
        .sidebar { width: 300px; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.05); overflow-y: auto; }
        .chat-main { flex: 1; display: flex; flex-direction: column; }
        .convo-list { padding: 1rem; }
        .convo-item { padding: 1rem; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.3s; }
        .convo-item:hover { background: #f9f9f9; }
        .messages { flex: 1; padding: 1rem; overflow-y: auto; background: #fafafa; }
        .message { margin-bottom: 1rem; padding: 0.8rem; border-radius: 10px; max-width: 60%; }
        .message.sent { background: #667eea; color: white; margin-left: auto; }
        .message.received { background: white; color: #333; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .input-area { padding: 1rem; background: white; border-top: 1px solid #eee; display: flex; gap: 1rem; }
        input[type="text"] { flex: 1; padding: 0.8rem; border: 1px solid #ddd; border-radius: 20px; }
        .btn { background: #667eea; color: white; border: none; padding: 0.8rem 1rem; border-radius: 20px; cursor: pointer; }
        nav { position: fixed; top: 0; width: 100%; background: #333; color: white; padding: 1rem; text-align: center; z-index: 10; }
        nav a { color: white; margin: 0 1rem; text-decoration: none; }
        @media (max-width: 768px) { body { flex-direction: column; } .sidebar { width: 100%; height: 200px; } }
    </style>
</head>
<body>
    <nav>
        <a href="index.php">Home</a>
    </nav>
    <div class="sidebar">
        <h3>Conversations</h3>
        <div class="convo-list">
            <?php foreach ($conversations as $convo) { ?>
                <div class="convo-item" onclick="window.location.href='messages.php?to=<?php echo $convo['id']; ?>'">
                    <?php echo htmlspecialchars($convo['username']); ?> (Last: <?php echo date('M j', strtotime($convo['timestamp'])); ?>)
                </div>
            <?php } ?>
            <?php if (empty($conversations)) { echo '<p>No messages yet.</p>'; } ?>
        </div>
    </div>
    <div class="chat-main">
        <?php if ($to_id) { 
            $to_user = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $to_user->execute([$to_id]);
            $to_name = $to_user->fetchColumn();
        ?>
            <h3>Chat with <?php echo htmlspecialchars($to_name); ?></h3>
            <div class="messages">
                <?php foreach ($messages as $msg) { ?>
                    <div class="message <?php echo $msg['from_user_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <strong><?php echo htmlspecialchars($msg['from_name']); ?>:</strong> <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        <?php if ($msg['is_file']) { echo ' [File Attached]'; } ?>
                        <small style="display: block; opacity: 0.7;"><?php echo date('H:i', strtotime($msg['timestamp'])); ?></small>
                    </div>
                <?php } ?>
            </div>
            <form method="POST" class="input-area">
                <input type="checkbox" name="is_file" id="file"> <label for="file">Simulate File Share</label>
                <input type="text" name="message" placeholder="Type a message..." required>
                <button type="submit" class="btn">Send</button>
            </form>
        <?php } else { ?>
            <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: #666;">
                <p>Select a conversation to start messaging.</p>
            </div>
        <?php } ?>
    </div>
 
    <script>
        // Internal JS - Auto-scroll to bottom
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelector('.messages');
            if (messages) {
                messages.scrollTop = messages.scrollHeight;
            }
            // Poll for new messages (simple every 5s)
            setInterval(function() {
                if (window.location.search.includes('to=')) {
                    window.location.reload();
                }
            }, 5000);
        });
    </script>
</body>
</html>
